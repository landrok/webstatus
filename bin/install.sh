#!/bin/bash -e

export LANG=C.UTF-8
set -o nounset

# Checks that we are in root or exit
if [ "$(id -u)" != "0" ]; then
  echo "This script must be run as root" 1>&2
  exit 1
fi


#*** CONFIG                                                         ***#
WSI_DATADIR="/dev/shm/webstatus"


#*** GLOBALS                                                        ***#
cd $(dirname "$(readlink -f "${BASH_SOURCE[0]}")") && cd ..

WSI_BASEDIR=$(pwd)
WSI_BINDIR="$WSI_BASEDIR/bin/"
WSI_WEBDIR="$WSI_BASEDIR/www"
WSI_APPDIR="$WSI_BASEDIR/app/"
WSI_HTTP_DEFAULT_HOST=$(ifconfig | grep 'inet ad' | grep -v '127.0.0.1'\
  | cut -d: -f2 | awk '{ print $1 }' | head -1)
WSI_DEFAULT_HTTPPORT="80"
WSI_HTTP_MSG="Application is running at: %s"
WSI_APACHEDIR="/etc/apache2"
WSI_USER=$(who am i | awk '{print $1}')

chmod +x -R "$WSI_BINDIR"

# Load helpers
. $WSI_BASEDIR/bin/install/rulem.sh
echo ""
rulem "[\e[100m Debian/Raspbian Web Status Installer \e[0m]"

# Checks that we are on a Raspbian or Debian OS
echo ""
echo "* System check"
if [ "$(lsb_release -s -d | grep Raspbian)" != "" ]; then
  echo "[$(lsb_release -s -d)]"
  WSI_LIBRARIES="php5 apache2 sysstat ifstat"
  WSI_OS="Raspbian"
elif [ "$(lsb_release -s -d | grep Debian)" != "" ]; then
  echo "[$(lsb_release -s -d)]"
  WSI_LIBRARIES="php5 apache2 sysstat ifstat lm-sensors"
  WSI_OS="Debian"
else
  lsb_release -a 1>&2
  echo "[ERROR] This does not seem to be Raspbian or Debian OS"
  exit 1
fi

# Locations
printf "\n* Locations\n[DATA] %s\n[WEB ] %s\n[APP ] %s\n" \
  "$WSI_DATADIR" "$WSI_WEBDIR" "$WSI_APPDIR"

# Install libraries
echo ""
echo "* Libraries"
apt-get install $WSI_LIBRARIES -qq || {
  echo "[ERROR] Installation failed, exiting."
  exit 1
}
echo "[INFO] Libraries \"$WSI_LIBRARIES\" were successfully installed"

#
# Configuration
#
echo ""
echo "* Configuration"
echo "[HELP] Press enter to keep default value or fill a custom value"
echo ""

# Webserver configuration
. $WSI_BASEDIR/bin/install/apache2.sh

# Install composer
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
php -r "if (hash_file('SHA384', 'composer-setup.php') === '669656bab3166a7aff8a7506b8cb2d1c292f042046c5a994c43155c0be6190fa0355160742ab2e1c88d40d5be660b410') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;"
php composer-setup.php
php -r "unlink('composer-setup.php');"
su $WSI_USER -c "php composer.phar update"



# Initialize data files
echo "[INFO] Initializing data files"
$WSI_BASEDIR/bin/webStatusCron.sh
#echo "" > "$(printf "$WSI_DATADIR%s" "ifstat.log")"
#chmod +r "$WSI_DATADIR"
#chmod 777 "$(printf "$WSI_DATADIR%s" "ifstat.log")"
chmod 777 -R "$(printf "%scache" "$WSI_APPDIR")"
#chmod 777 -R "$WSI_WEBDIR"


# Starting web service
echo "[INFO] Restarting web server"
if [ "$(service apache2 status | grep active)" = "" ]; then
  service apache2 start
else
  service apache2 reload
fi

# Add to crontab
echo "[INFO] Crontab configuration"
TMP=${TMPDIR:-/tmp}/webstatus-cron.$$
trap "rm -f $TMP; exit 1" 0 1 2 3 13 15
crontab -l | sed '/webStatusCron.sh/d' > $TMP
printf "# webStatusCron.sh | %s \n" "$WSI_BINDIR" >> $TMP
printf "@reboot %swebStatusCron.sh > %s/log.log 2>&1\n" \
  "$WSI_BINDIR" "$WSI_DATADIR" >> $TMP
printf "*/1 * * * * %swebStatusCron.sh > %s/log.log 2>&1\n" \
  "$WSI_BINDIR" "$WSI_DATADIR" >> $TMP
crontab < $TMP
rm -f $TMP
trap 0

# Print HTTP message
echo ""
rulem "[ \e[32mInstallation success\e[0m ]"
echo ""
if [ "$WSI_HTTP_PORT" = "80" ]; then
  printf "[INFO] $WSI_HTTP_MSG \n" "http://$WSI_HTTP_HOST/webstatus/"
else
  printf "[INFO] $WSI_HTTP_MSG \n" \
  "http://$WSI_HTTP_HOST:$WSI_HTTP_PORT/webstatus/"
fi
echo ""
exit 0
