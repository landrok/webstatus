#!/bin/bash

#*** install.sh                                                     ***#
#
# This script installs shellcheck
#

set -eo pipefail

# shellcheck source=bin/lib/bash-utils.sh disable=1091
source "bin/lib/bash-utils.sh"

curl -Lso \
  /usr/bin/shellcheck \
  https://github.com/caarlos0/shellcheck-docker/releases/download/v0.4.6/shellcheck

chmod +x /usr/bin/shellcheck

success "shellcheck installed"
