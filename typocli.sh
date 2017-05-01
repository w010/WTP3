#!/bin/sh

# vagrant ssh --command "docker exec -it berglanddev_php_1 php /var/www/htdocs/typo3/cli_dispatch.phpsh $1 $2 $3 $4"

docker exec -it berglanddev_php_1 php /var/www/htdocs/typo3/cli_dispatch.phpsh $1 $2 $3 $4

read -p "Press enter to continue" nothing