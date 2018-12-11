#!/bin/sh

# use example:
# crontab add: `25,55 7-23 * * * /var/www/html/yyb/crontab_script5.sh`

app_path=`cd $(dirname $0) && pwd`
cd $app_path
php think alertappoint