#!/bin/bash -e

set -o nounset

[[ -z "${WSI_DATADIR+x}" ]] && {
  echo "[ERROR] \"bin/cron/loadConfig.sh\" should not be called directly"
  echo "[HELP ] Run \"bin/webStatusCron.sh\" instead."
  exit 1
}

# Load global and global-custom contents
WS_GLOBAL_CONFIG=""

[ -f "$WS_CONFIGDIR/global-custom.ini.php" ]                           \
  && WS_GLOBAL_CONFIG=${WS_GLOBAL_CONFIG}$(cat "$WS_CONFIGDIR/global-custom.ini.php")
  
[ -f "$WS_CONFIGDIR/global.ini.php" ]                                  \
  && WS_GLOBAL_CONFIG=${WS_GLOBAL_CONFIG}$(cat "$WS_CONFIGDIR/global.ini.php")
