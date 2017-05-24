#!/bin/bash -e 

set -o nounset
export LANG=C.UTF-8

# Checks that we are in root or exit
if [ "$(id -u)" != "0" ]; then
  echo "This script must be run as root" 1>&2
  exit 1
fi

#*** CONFIG                                                         ***#
WSI_DATADIR="/dev/shm/webstatus"

#*** GLOBALS                                                        ***#
cd "$(dirname "$(readlink -f "${BASH_SOURCE[0]}")")" && cd ..
WS_PWD=$(pwd)
export WS_CONFIGDIR="$WS_PWD/app/config"
WS_BINDIR="$WS_PWD/bin"

#*** FUNCTIONS                                                      ***#
# shellcheck source=bin/lib/bash-utils.sh disable=1091
source "bin/lib/bash-utils.sh"

#*** CONFIG                                                         ***#
# shellcheck source=bin/cron/loadConfig.sh disable=1091
source "$WS_BINDIR/cron/loadConfig.sh"

# Used by remote client mode
WS_REMOTE_SERVER_URL=$(awk -F "=" '/^remote.url/ {
    gsub(/"/, "", $2);
    print $2
  }'                                                                   \
  <<< "$WS_GLOBAL_CONFIG" | head -1) 
export WS_REMOTE_SERVER_URL

#*** MAIN                                                           ***#
# Create DATA dir
[ -d "$WSI_DATADIR" ] || mkdir -p "$WSI_DATADIR"

# Remote server mode
REMOTESRV_CFG=$(awk -F "=" '/^remote.server/ {
    gsub(/"/, "", $2);
    print $2
  }'                                                                   \
  <<< "$WS_GLOBAL_CONFIG" | head -1) 
if [ ! -z "$REMOTESRV_CFG" ]; then
  # shellcheck source=bin/cron/remoteServer.sh disable=1091
  source "$WS_BINDIR/cron/remoteServer.sh"
  exit
fi

# Create a local history file
[ -f "$WSI_DATADIR/history.json" ] || {
  touch "$WSI_DATADIR/history.json"
  chmod +rx "$WSI_DATADIR/history.json"
}

# Make all metrics
for metric in status processes uptime top temperature os servers memory\
              hdd mpstat users interfaces tcp-sockets iptables ifstat
do
  # shellcheck source=bin/command/$metric.sh disable=1091
  source "bin/command/$metric.sh" > "$WSI_DATADIR/$metric.log"
done

# Dump some logs
awk -F "=" '/^logs.pattern.*/ {
      gsub(/"/, "", $2);
      gsub(/logs.pattern./, "", $1);
      print $1 "|" $2;
    }' <<< "$WS_GLOBAL_CONFIG"                                         \
  | while IFS= read -r line; do
  WS_LOG_FILE=$(  echo "$line" | awk -F "|" '{print $1}')
  WS_LOG_SOURCE=$(echo "$line" | awk -F "|" '{print $2}')
  
  echo "" > "$WSI_DATADIR/$WS_LOG_FILE.log"
  for file in ${WS_LOG_SOURCE}
  do
    {
      echo ">>> ${file} <<<";
      if [ -f "${file}" ]; then
        tail -n 15 "${file}";
      else
        echo "File not found";
      fi
      echo "";
    } >> "$WSI_DATADIR/$WS_LOG_FILE.log"
  done
done 

# Update history
php "$WS_PWD/app/controllers/history.php"

# Remote client mode
REMOTECLI_CFG=$(awk -F "=" '/^remote.client/ {
    gsub(/"/, "", $2);
    print $2
  }'                                                                   \
  <<< "$WS_GLOBAL_CONFIG" | head -1) 
if [ ! -z "$REMOTECLI_CFG" ]; then
  # shellcheck source=bin/cron/remoteClient.sh disable=1091
  source "bin/cron/remoteClient.sh"
fi
