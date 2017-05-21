#!/bin/bash -e

set -o nounset

[[ -z "${WSI_DATADIR+x}" ]] && {
  echo "[ERROR] \"bin/command/servers.sh\" should not be called directly"
  echo "[HELP ] Run \"bin/webStatusCron.sh\" instead."
  exit 1
}

#*** MAIN                                                           ***#
netstat -lntp                                                          \
 | sed -n '2,40p'                                                      \
 | sort -k 4 -r
