#!/bin/bash -e

set -o nounset

[[ -z "${WSI_DATADIR+x}" ]] && {
  echo "[ERROR] \"bin/command/processes.sh\" should not be called directly"
  echo "[HELP ] Run \"bin/webStatusCron.sh\" instead."
  exit 1
}

#*** MAIN                                                           ***#
PROCESSES_CFG=$(awk -F "=" '/^processes.pattern/ {
    gsub(/"/, "", $2);
    print $2
  }' <<< "$WS_GLOBAL_CONFIG" | head -1) 

if test -z "$PROCESSES_CFG"; then
  PROCESSES_PATTERN=""
else
  PROCESSES_PATTERN=$(printf "MEM COMMAND|%s" "$PROCESSES_CFG")
fi

WS_PROCESSES=$(ps -A f -o pid,time,pcpu,pmem,command)
echo "$WS_PROCESSES"                                                   \
  | grep -E "$PROCESSES_PATTERN"
