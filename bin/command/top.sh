#!/bin/bash -e

set -o nounset

[[ -z "${WSI_DATADIR+x}" ]] && {
  echo "[ERROR] \"bin/command/top.sh\" should not be called directly"
  echo "[HELP ] Run \"bin/webStatusCron.sh\" instead."
  exit 1
}

#*** MAIN                                                           ***#
top -b -n 1 -o +%CPU                                                   \
  | sed -n '7,40p'                                                     \
  | body sort -k9rn -k10rn                                             \
  | awk '{printf "%6s %-7s %-4s %-4s %-s\n",$1,$2,$9,$10,$NF}'
