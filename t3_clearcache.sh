#!/bin/bash

#PHPBIN="php_cli"
#PHPBIN="php"
PHPBIN="php56"
#PHPBIN="php7"
#PHPBIN="php72"

echo "-- clear cache cmd:"
echo "$PHPBIN ./typo3/cli_dispatch.phpsh extbase cacheapi:clearallcaches"

$PHPBIN ./typo3/cli_dispatch.phpsh extbase cacheapi:clearallcaches
