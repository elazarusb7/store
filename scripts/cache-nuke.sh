#!/usr/bin/env bash
## This script truncates all Drupal cache tables. Standard Drupal cache clearing tools do not.

## See if we're using lando or not
`lando drush > /dev/null 2>&1`

err=$?

if [[ $err -ge 1 ]]; then
  cmd_prefix=''
  else
  cmd_prefix='lando'
fi

`${cmd_prefix} drush sql:query 'call delcache()' > /dev/null 2>&1`

err=$?

if [[ $err -ge 1 ]]; then

  echo "The stored procedure doesn't exist. I will create it."
  script_dir=`dirname $0`

  `${cmd_prefix}  drush sql:cli < $script_dir/stored_procedure_cache_nuke.sql`
  echo "Stored procedure created. Running cache nuke..."

  `${cmd_prefix}  drush sql:query 'call delcache()' > /dev/null 2>&1`

fi

echo "All Drupal cache tables, and more, emptied..."
