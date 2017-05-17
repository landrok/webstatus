#!/usr/bin/env bash

#*** build.sh                                                       ***#
# 
# This script lints all .sh files of the git repository
#

set -eo pipefail
test -n "${DEBUG:-}" && set -x

# shellcheck source=bin/lib/bash-utils.sh disable=1091
source "bin/lib/bash-utils.sh"

check() {
  local script="$1"
  shellcheck "$script" || fail "$script"
  success "$script"
}

find_scripts() {
  git ls-tree -r HEAD | grep -E '^1007|.*\..*sh$' | awk '{print $4}'
}

is_compatible() {
  head -n1 "$1" | grep -E -w "sh|bash|ksh" > /dev/null 2>&1
}

check_all_executables() {
  echo "Linting all executables and .*sh files..."
  find_scripts | while read -r script; do
    if is_compatible "$script"; then
      check "$script"
    else
      info "Skipping $script..."
    fi
  done
}

# if being executed, check all executables, otherwise do nothing
if [ $SHLVL -gt 1 ]; then
  check_all_executables
else
  return 0
fi
