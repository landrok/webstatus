#!/bin/bash -e

set -o nounset

[[ -z "${WSI_DATADIR+x}" ]] && {
  echo "[ERROR] \"bin/command/os.sh\" should not be called directly"
  echo "[HELP ] Run \"bin/webStatusCron.sh\" instead."
  exit 1
}

#*** MAIN                                                           ***#
lsb_release -d                                                         \
  | awk '{ gsub("Description:\t", "", $0); print }';
uname -r >> "$WSI_DATADIR/os.log";
echo "$CPU_TEMPERATURE";
