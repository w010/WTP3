#!/bin/sh

# vagrant ssh --command "docker exec -it malerpraxisdev_php_1 php /var/www/htdocs/typo3/cli_dispatch.phpsh $1 $2 $3 $4"

docker exec -it malerpraxisdev_php_1 php /var/www/htdocs/typo3/cli_dispatch.phpsh $1 $2 $3 $4

#_docker/php_proxy.sh php /var/www/trunk/typo3/cli_dispatch.phpsh extbase help
_docker/php_proxy.sh php /var/www/htdocs/typo3/cli_dispatch.phpsh extbase help

# read -p "Press enter to continue" nothing