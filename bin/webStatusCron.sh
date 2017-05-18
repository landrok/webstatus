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
  }'                                                                 \
  <<< "$WS_GLOBAL_CONFIG" | head -1) 
export WS_REMOTE_SERVER_URL

#*** MAIN                                                           ***#
# Create DATA dir
[ -d "$WSI_DATADIR" ] || mkdir -p "$WSI_DATADIR"

# Remote server mode
REMOTESRV_CFG=$(awk -F "=" '/^remote.server/ {
    gsub(/"/, "", $2);
    print $2
  }'                                                                 \
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

# temperature
[ -f "/sys/class/thermal/thermal_zone0/temp" ]                         \
  && CPU_TEMPERATURE=$(printf "%s°C"                                   \
    $(($(cat /sys/class/thermal/thermal_zone0/temp)/1000)))            \
  || CPU_TEMPERATURE="-"
[ -f "/opt/vc/bin/vcgencmd" ]                                          \
  && GPU=$(/opt/vc/bin/vcgencmd measure_temp)                          \
  || GPU="-"

# date & hostname
echo "$(date) @ $(hostname)" > "$WSI_DATADIR/status.log"

# OS Description
lsb_release -d                                                         \
  | awk '{ gsub("Description:\t", "", $0); print }'                    \
  > "$WSI_DATADIR/os.log"
uname -r >> "$WSI_DATADIR/os.log"
echo "$CPU_TEMPERATURE" >> "$WSI_DATADIR/os.log"

# uptime
echo "$(echo "Started at"; uptime -s; uptime -p)" | xargs              \
  > "$WSI_DATADIR/uptime.log"

# processes
# shellcheck source=bin/command/processes.sh disable=1091
source "bin/command/processes.sh"

# top 15 processes ordered by CPU usage
top -b -n 1 -o +%CPU                                                   \
  | sed -n '7,40p'                                                     \
  | body sort -k9rn -k10rn                                             \
  | awk '{printf "%6s %-7s %-4s %-4s %-s\n",$1,$2,$9,$10,$NF}'         \
  > "$WSI_DATADIR/top.log"

# Temperature
printf "CPU => %s\nGPU => %s" "$CPU_TEMPERATURE" "$GPU"                \
  > "$WSI_DATADIR/temperature.log"

# servers
netstat -lntp                                                          \
 | sed -n '2,40p'                                                      \
 | sort -k 4 -r                                                        \
 > "$WSI_DATADIR/servers.log"

# memory
free -mh > "$WSI_DATADIR/memory.log"

# disk
df -h > "$WSI_DATADIR/hdd.log"

# mpstats
mpstat -P ALL > "$WSI_DATADIR/mpstat.log"

# users
who -sH > "$WSI_DATADIR/users.log"

# Interfaces
/sbin/ifconfig -a > "$WSI_DATADIR/interfaces.log"

# Network
ss -t > "$WSI_DATADIR/tcp-sockets.log"

# Firewall
/sbin/iptables -L > "$WSI_DATADIR/iptables.log"

# In / Out
ifstat -a -T 2 1 > "$WSI_DATADIR/ifstat.log"

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
  }'                                                                 \
  <<< "$WS_GLOBAL_CONFIG" | head -1) 
if [ ! -z "$REMOTECLI_CFG" ]; then
  # shellcheck source=bin/cron/remoteClient.sh disable=1091
  source "bin/cron/remoteClient.sh"
fi
