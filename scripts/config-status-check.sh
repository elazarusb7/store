#!/bin/bash
# Confirm that config import "matches" config export. After importing config, there
# should be no unexpected output when config export is run.

# WARNING: This will obviously break if script is moved.
PROJECT_ROOT="${0%/*}/.."
cd $PROJECT_ROOT

# This is a list of files that 'normally' get exported by csex but that we don't care about
# Git wildcarding OK
BASE_ASSUME_UNCHANGED=(
  'config/sync/config_split.config_split.*'
)

set_assume_unchanged() {
  arr=("$@")
  for file in "${arr[@]}"; do
    git update-index --assume-unchanged $file
  done
}

unset_assume_unchanged() {
  arr=("$@")
  for file in "${arr[@]}"; do
    git update-index --no-assume-unchanged $file
  done
}

# We want to preserve these git flags for the user, so get a snapshot for
# later restoration
list_user_unchanged=`git ls-files -v | egrep '^h' | sed 's/^h *//g'`

# Convert newline seperated output above into a bash +=(item)
# TODO: I have no idea what this is doing or why it works
USER_ASSUME_UNCHANGED=($(echo $list_user_unchanged | tr ";" "\n"))

echo "Setting assume unchanged flags"
set_assume_unchanged ${BASE_ASSUME_UNCHANGED[*]}

# Use lando if it exists, else just use drush.
if hash lando 2>/dev/null; then
  DRUSH_PREFIX="lando "
else
  DRUSH_PREFIX=""
fi

# TODO: If lando exists, else
echo "Importing configuration"
$DRUSH_PREFIX drush -y cim &>/dev/null
$DRUSH_PREFIX drush -y cim &>/dev/null
echo "Exporting configuration"
$DRUSH_PREFIX drush -y csex &>/dev/null

RAW_CHANGE_COUNT=`git status --porcelain | wc -l`
RAW_CHANGE_LIST=`git status --porcelain`

# Reset git flags to as they were
echo "Unsetting assume unchanged flags"
unset_assume_unchanged ${BASE_ASSUME_UNCHANGED[*]}
echo "Restoring user original assume unchanged flags"
set_assume_unchanged ${USER_ASSUME_UNCHANGED[*]}

if (( $RAW_CHANGE_COUNT > 0 )); then
   echo "The following configuration diffs are unexpected:"
   echo "$RAW_CHANGE_LIST"
   echo "Test failed. Please check the following things."
   echo " * Make sure you have committed outstanding changes."
   echo " * Be sure to reset your git environment (using git reset)."
   echo " * Run composer install."
   echo " * If the above things don't fix it, you need to review the configuration files."
   exit 1
else
  echo "Configuration looks good, ship it!"
  exit 0
fi
