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
cd "$(dirname "$(readlink -f "${BASH_SOURCE[0]}")")" && cd ..

WSI_BASEDIR=$(pwd)
WSI_BINDIR="$WSI_BASEDIR/bin"
WSI_WEBDIR="$WSI_BASEDIR/www"
WSI_APPDIR="$WSI_BASEDIR/app"
WSI_HTTP_DEFAULT_HOST=$(ifconfig | grep 'inet ad'                      \
  | grep -v '127.0.0.1'                                                \
  | cut -d: -f2 | awk '{ print $1 }' | head -1)
export WSI_HTTP_DEFAULT_HOST
export WSI_DEFAULT_HTTPPORT="80"
export WSI_APACHEDIR="/etc/apache2"
WSI_USER=$(who am i | awk '{print $1}')

chmod +x -R "$WSI_BINDIR"
chown -R "$WSI_USER:www-data" "$WSI_BASEDIR"

#*** ARGS                                                           ***#
while getopts y option
do
  case "${option}"
  in
  y) WSI_AUTOINSTALL="1";;
  esac
done

if [ -z ${WSI_AUTOINSTALL+x} ]; then
  WSI_AUTOINSTALL="0"
fi

#*** FUNCTIONS                                                      ***#
# shellcheck source=bin/install/rulem.sh
source "$WSI_BASEDIR/bin/install/rulem.sh"

#*** MAIN                                                           ***#
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
  WSI_LIBRARIES="php5 apache2 sysstat ifstat lm-sensors curl"
  WSI_OS="Debian"
elif [ "$(lsb_release -s -d | grep Ubuntu)" != "" ]; then
  echo "[$(lsb_release -s -d)]"
  WSI_LIBRARIES="php5 apache2 sysstat ifstat lm-sensors curl"
  WSI_OS="Ubuntu"
else
  lsb_release -a 1>&2
  echo "[ERROR] This does not seem to be Raspbian or Debian OS"
  exit 1
fi

# Locations
printf "\n* Locations\n[DATA] %s\n[WEB ] %s\n[APP ] %s\n"              \
  "$WSI_DATADIR" "$WSI_WEBDIR" "$WSI_APPDIR"

# Install libraries
echo ""
echo "* Libraries"
IFS=' ' read -ra PACKETS <<< "$WSI_LIBRARIES"
for p in "${PACKETS[@]}"; do
  if [ "$(dpkg -l | grep "$p")" == "" ]; then
    apt-get install "$p" -qq || {
      echo "[ERROR] Installation failed, exiting.";
      exit 1;
    }
  else
    echo "[INFO] $p already installed"
  fi
done

echo "[INFO] $WSI_OS Libraries \"$WSI_LIBRARIES\" were successfully installed"

#*** Configuration                                                  ***#
echo ""
echo "* Configuration"
if [ "$WSI_AUTOINSTALL" = "1" ]; then
  echo "[HELP] Autoinstall will keep default values"
else
  echo "[HELP] Press enter to keep default value or fill a custom value"
fi
echo ""

# Webserver configuration
# shellcheck source=bin/install/apache2.sh
source "$WSI_BASEDIR/bin/install/apache2.sh"

# Install composer
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
php -r "if (hash_file('SHA384', 'composer-setup.php') === '669656bab3166a7aff8a7506b8cb2d1c292f042046c5a994c43155c0be6190fa0355160742ab2e1c88d40d5be660b410') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;"
php composer-setup.php
php -r "unlink('composer-setup.php');"
su "$WSI_USER" -c "php composer.phar update --no-dev -o"

# Initialize data files
echo "[INFO] Initializing data files"
"$WSI_BASEDIR/bin/webStatusCron.sh"
[ -d "$WSI_APPDIR/cache" ] || mkdir "$WSI_APPDIR/cache"
chmod 777 -R "$WSI_APPDIR/cache"

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
trap 'rm -f "$TMP"; exit 1' 0 SIGHUP SIGINT SIGQUIT SIGPIPE SIGTERM
crontab -l | sed '/webStatusCron.sh/d' > "$TMP"
{
  printf "\n# webStatusCron.sh | %s \n"      "$WSI_BINDIR";
  printf "@reboot %s/webStatusCron.sh\n"     "$WSI_BINDIR";
  printf "*/1 * * * * %s/webStatusCron.sh\n" "$WSI_BINDIR";
} >> "$TMP"
crontab < "$TMP"
rm -f "$TMP"
trap 0

#*** Print success message                                          ***#
echo ""
rulem "[ \e[32mInstallation success\e[0m ]"
echo ""
if [ "$WSI_HTTP_PORT" = "80" ]; then
  printf "[INFO] Application is running at: %s \n"                     \
  "http://$WSI_HTTP_HOST/webstatus/"
else
  printf "[INFO] Application is running at: %s \n"                     \
  "http://$WSI_HTTP_HOST:$WSI_HTTP_PORT/webstatus/"
fi
echo ""
