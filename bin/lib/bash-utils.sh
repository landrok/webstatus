#!/usr/bin/env bash

#*** bash-utils.sh                                                  ***#
# 
# This script is a collection of shell/bash functions
#
# FUNCTION  : USAGE
#
# success() : success "My message"
# fail()    : fail "My message"
# info()    : info "My message"
# rulem()   : rulem "My message"
# rulem()   : rulem "My message" "*"
# body()    : ps | body grep somepattern
#
#***                                                                ***#
set -eo pipefail
test -n "${DEBUG:-}" && set -x

## print a success message | status=OK, color=green
#
# @param string $1
success() {
  printf "\r  [ \033[00;32mOK\033[0m ] %s\n" "$1"
}

## print a failure message | status=FAIL, color=red
#
# @param string $1
# @throws an exit status code 1
#
fail() {
  printf "\r  [\033[0;31mFAIL\033[0m] %s\n" "$1"
  exit 1
}

## print an info message | status=!!, color=blue
#
# @param string $1
info() {
  printf "\r  [ \033[00;34m!!\033[0m ] %s\n" "$1"
}

## print horizontal ruler with message
#
# @param string $1 A message
# @param string $2 An optional ruler sign
rulem ()  {
  if [ $# -eq 0 ]; then
    echo "Usage: rulem MESSAGE [RULE_CHARACTER]"
    return 1
  fi

  # Fill line with ruler character ($2, default "-"), reset cursor, move 2 cols right, print message
  printf -v _hr "%*s" "$(tput cols)" && echo -en "${_hr// /${2--}}" && echo -e "\r\033[2C$1"
}

## print the header (the first line of input)
# and then run the specified command on the body (the rest of the input)
# use it in a pipeline, e.g. ps | body grep somepattern
body() {
  IFS= read -r header
  printf '%s\n' "$header"
  "$@"
}
