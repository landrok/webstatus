#!/bin/bash -e

set -o nounset

[[ -z "${WSI_BASEDIR+x}" ]] && {
  echo "[ERROR] \"bin/install/apache2.sh\" should not be called directly"
  echo "[HELP ] Run \"bin/install.sh\" instead."
  exit 1
}

#
# Apache2 configurator
#

# Is Apache already configured?
if [ "$(find "$WSI_APACHEDIR/sites-available" -type f -name 'webstatus.conf')" = "webstatus.conf" ]; then
  echo -n "[PARA] There is already a VHOST configuration file for WebStatus, overwrite? [N/y] "
  read -r WSI_APACHEOVERRIDE
  if [ "$WSI_APACHEOVERRIDE" != "y" ]; then
    # @todo Have to grep PORT and HOST in the configuration file
    # instead of setting with default values
    WSI_HTTP_HOST="$WSI_HTTP_DEFAULT_HOST"
    WSI_HTTP_PORT="$WSI_DEFAULT_HTTPPORT"
    echo "[INFO] Keeping Apache VHOST configuration"
    return 0
  fi
fi

# Hostname
echo -n "[PARA] Enter web hostname[$WSI_HTTP_DEFAULT_HOST]: "
read -r WSI_CUSTOMHOST
if [ "$WSI_CUSTOMHOST" = "" ]; then
  WSI_HTTP_HOST="$WSI_HTTP_DEFAULT_HOST"
else
  WSI_HTTP_HOST="$WSI_CUSTOMHOST"
fi

# Port
echo -n "[PARA] Enter web port[$WSI_DEFAULT_HTTPPORT]: "
read -r WSI_CUSTOMPORT
if [ "$WSI_CUSTOMPORT" = "" ]; then
  WSI_HTTP_PORT="$WSI_DEFAULT_HTTPPORT"
else
  WSI_HTTP_PORT="$WSI_CUSTOMPORT"
fi

if [ "$WSI_HTTP_PORT" = "80" ]; then
  WSI_HTTPLISTEN=""
else
  WSI_HTTPLISTEN="Listen $WSI_HTTP_PORT"
fi

# Generate conf.
echo "[CONF] Configuring Apache2 Web Server..."
cat <<EOF > "$WSI_APACHEDIR/sites-available/webstatus.conf"
$WSI_HTTPLISTEN
<VirtualHost *:$WSI_HTTP_PORT>
  ServerName $WSI_HTTP_HOST
  Alias /webstatus/ "$WSI_WEBDIR/"

  <Directory />
    Options -Indexes
    Options MultiViews
    AllowOverride None
    Order allow,deny
    Allow from all
    Require all granted
  </Directory>

  ErrorLog /var/log/apache2/error.webstatus.log
  LogLevel warn
  CustomLog /var/log/apache2/access.webstatus.log combined

</VirtualHost>
EOF

a2ensite webstatus.conf > /dev/null

if [ "$(apache2ctl configtest 2>&1 | grep 'Syntax OK')" = "" ]; then
  echo "[ERROR] Installation failed, exiting."
  exit 1
fi
echo "[INFO] Configuration Syntax OK"
