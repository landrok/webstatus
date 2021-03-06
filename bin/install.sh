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
WSI_CONFIG_GLOBAL_CUSFILE="global-custom.ini.php"
WSI_USER=$(who am i | awk '{print $1}')
WSI_HTTP_DEFAULT_HOST=$(ifconfig | grep 'inet ad'                      \
  | grep -v '127.0.0.1'                                                \
  | cut -d: -f2 | awk '{ print $1 }' | head -1)

export WSI_HTTP_DEFAULT_HOST
export WSI_DEFAULT_HTTPPORT="80"
export WSI_APACHEDIR="/etc/apache2"

# Config file must exist
[ -f "$WSI_APPDIR/config/global.ini.php" ] || {
  printf '"%s" must exists.' "$WSI_APPDIR/config/global.ini.php"
  exit 1
}

# Create a custom file if not existing
[ -f "$WSI_APPDIR/config/$WSI_CONFIG_GLOBAL_CUSFILE" ] || {
  cp "$WSI_APPDIR/config/global.ini.php"                               \
  "$WSI_APPDIR/config/$WSI_CONFIG_GLOBAL_CUSFILE"
}

chmod +x -R "$WSI_BINDIR"
chown -R "$WSI_USER:www-data" "$WSI_BASEDIR"

# Travis CI
[[ -z "${TRAVIS_PHP_VERSION+x}" ]] || {
  export PATH=$PATH:/home/travis/.phpenv/bin
  eval "$(phpenv init -)"
}

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
# shellcheck source=bin/lib/bash-utils.sh disable=1091
source "bin/lib/bash-utils.sh"

#*** MAIN                                                           ***#
echo ""
rulem "[\e[100m Debian, Raspbian & Ubuntu WebStatus Installer \e[0m]"

# Check that we are on a Raspbian, Ubuntu or Debian OS
echo ""
echo "* System check"
if [ "$(lsb_release -s -d | grep Raspbian)" != "" ]; then
  echo "[$(lsb_release -s -d)]"
  WSI_LIBRARIES="apache2 sysstat ifstat"
  WSI_OS="Raspbian"
elif [ "$(lsb_release -s -d | grep Debian)" != "" ]; then
  echo "[$(lsb_release -s -d)]"
  WSI_LIBRARIES="apache2 sysstat ifstat lm-sensors curl"
  WSI_OS="Debian"
elif [ "$(lsb_release -s -d | grep Ubuntu)" != "" ]; then
  echo "[$(lsb_release -s -d)]"
  WSI_LIBRARIES="apache2 sysstat ifstat lm-sensors curl"
  WSI_OS="Ubuntu"
else
  lsb_release -a 1>&2
  echo "[ERROR] This does not seem to be Raspbian, Ubuntu or Debian OS"
  exit 1
fi

# Locations
printf "\n* Locations\n[DATA] %s\n[WEB ] %s\n[APP ] %s\n"              \
  "$WSI_DATADIR" "$WSI_WEBDIR" "$WSI_APPDIR"

# Install libraries
echo ""
echo "* Libraries"

if [[ -z "${TRAVIS_PHP_VERSION+x}" && "$(dpkg -l | grep php)" = "" ]]; then
  echo "[INFO] Installing php"
  apt-get install php7.0 php7.0-curl -qq || {
    echo "[ERROR] Installation failed, exiting.";
    exit 1;
  }
else
  echo "[INFO] php already installed"
fi

IFS=' ' read -ra PACKETS <<< "$WSI_LIBRARIES"
for p in "${PACKETS[@]}"; do
  if [ "$(dpkg -l | grep "$p")" = "" ]; then
    echo "[INFO] Installing $p"
    apt-get install "$p" -qq || {
      echo "[ERROR] Installation failed, exiting.";
      exit 1;
    }
  else
    echo "[INFO] $p already installed"
  fi
done

echo "[INFO] $WSI_OS Libraries were successfully installed"

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
# shellcheck source=bin/install/apache2.sh disable=1091
source "$WSI_BASEDIR/bin/install/apache2.sh"

# Install composer
echo "[INFO] Composer install"
if [[ -z "${TRAVIS_PHP_VERSION+x}" ]]; then
  # Classic
  apt-get install composer
  su "$WSI_USER" -c "php composer.phar update --no-dev -o"
else
  # Travis CI
  echo "[INFO] Skipping..."
fi

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
set +e
crontab -l                                                             \
  | sed '/no crontab for root/d'                                       \
  | sed '/webStatusCron.sh/d' > "$TMP"
{
  printf "\n# webStatusCron.sh | %s \n"      "$WSI_BINDIR";
  printf "@reboot %s/webStatusCron.sh\n"     "$WSI_BINDIR";
  printf "*/1 * * * * %s/webStatusCron.sh\n" "$WSI_BINDIR";
} >> "$TMP"
crontab < "$TMP"
rm -f "$TMP"
set -e
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
