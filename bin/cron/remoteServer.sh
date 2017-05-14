#!/bin/bash -e

set -o nounset

[[ -z "${WSI_DATADIR+x}" ]] && {
  echo "[ERROR] \"bin/cron/remoteServer.sh\" should not be called directly"
  echo "[HELP ] Run \"bin/webStatusCron.sh\" instead."
  exit 1
}

WC_ZIPNAME="$WS_PWD/app/cache/webstatus.tar.gz"

echo "Server remote mode"

# ZIP exists
if [ ! -f "$WC_ZIPNAME" ]; then
  echo "$WC_ZIPNAME does not exists"
  exit
fi

# Archive is too old
if [ "$(find "$WS_PWD/app/cache/" -mmin -2 -type f -name "webstatus.tar.gz")" = "" ]; then
  echo "$WC_ZIPNAME is too old. Exiting"
  exit
fi

# Unarchive data
echo "Unarchiving $WC_ZIPNAME "
cd "$WSI_DATADIR"
tar -zxf "$WC_ZIPNAME"
cd "$WS_PWD"
