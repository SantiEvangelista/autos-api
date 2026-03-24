#!/bin/sh
set -e

# Configura Xdebug mode dinámicamente desde env var
XDEBUG_INI="/usr/local/etc/php/conf.d/xdebug.ini"
XDEBUG_MODE="${XDEBUG_MODE:-off}"

cat > "$XDEBUG_INI" <<EOF
zend_extension=xdebug
xdebug.mode=${XDEBUG_MODE}
xdebug.start_with_request=yes
xdebug.client_host=host.docker.internal
xdebug.client_port=9003
xdebug.discover_client_host=false
xdebug.idekey=VSCODE
xdebug.log_level=0
EOF

exec "$@"
