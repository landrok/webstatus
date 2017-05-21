#!/bin/bash -e

set -o nounset

[[ -z "${WSI_DATADIR+x}" ]] && {
  echo "[ERROR] \"bin/command/temperature.sh\" should not be called directly"
  echo "[HELP ] Run \"bin/webStatusCron.sh\" instead."
  exit 1
}

#*** MAIN                                                           ***#
[ -f "/sys/class/thermal/thermal_zone0/temp" ]                         \
  && CPU_TEMPERATURE=$(printf "%s°C"                                   \
    $(($(cat /sys/class/thermal/thermal_zone0/temp)/1000)))            \
  || CPU_TEMPERATURE="-"
[ -f "/opt/vc/bin/vcgencmd" ]                                          \
  && GPU=$(/opt/vc/bin/vcgencmd measure_temp)                          \
  || GPU="-"

printf "CPU => %s\nGPU => %s" "$CPU_TEMPERATURE" "$GPU"
