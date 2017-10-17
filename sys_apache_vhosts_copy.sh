#!/bin/sh

docker cp   /var/www/htdocs/_docker/support-services/apache/dev/httpd-vhosts.conf   malerpraxisdev_apache_1:/usr/local/apache2/conf/extra/httpd-vhosts.conf
