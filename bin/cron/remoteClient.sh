#!/bin/bash -e

#
# This script compare last status files
# When a status file is new, it builds a new archive
# and send it to the webstatus server
#

set -o nounset

[[ -z "${WSI_DATADIR+x}" ]] && {
  echo "[ERROR] \"bin/cron/remoteClient.sh\" should not be called directly"
  echo "[HELP ] Run \"bin/webStatusCron.sh\" instead."
  exit 1
}

WC_ZIPNAME="$WS_PWD/app/cache/webstatus.tar.gz"
WC_STATUSNAME="$WSI_DATADIR/status.log"

echo "Client remote mode"

# Status exists
if [ ! -f "$WC_STATUSNAME" ]; then
  echo "$WC_STATUSNAME does not exists"
  exit
fi

# Archive is more recent
if test "$WC_ZIPNAME" -nt "$WC_STATUSNAME"
then
  echo "$WC_ZIPNAME is NEWER than $WC_STATUSNAME"
  exit
fi

# Archive exists
if [ ! -f "$WC_STATUSNAME" ]; then
  echo "$WC_STATUSNAME does not exists"
  exit
fi

# Archive data
echo "Archiving $WSI_DATADIR"
cd "$WSI_DATADIR"
rm "$WC_ZIPNAME"
tar -zcf "$WC_ZIPNAME" ./*glob*
chmod 777 "$WC_ZIPNAME" 
cd "$WS_PWD"

# Sending to server
curl -iv -X POST -H "Content-Type: multipart/form-data" -F \
 "data=@${WS_PWD}/app/cache/webstatus.tar.gz" "$WS_REMOTE_SERVER_URL"
