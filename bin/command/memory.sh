#!/bin/bash -e

set -o nounset

[[ -z "${WSI_DATADIR+x}" ]] && {
  echo "[ERROR] \"bin/command/memory.sh\" should not be called directly"
  echo "[HELP ] Run \"bin/webStatusCron.sh\" instead."
  exit 1
}

#*** MAIN                                                           ***#
free -mh
