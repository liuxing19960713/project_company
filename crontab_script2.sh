#!/bin/sh

# use example:
# crontab add: `0,30 8-22 * * * /var/www/html/yyb/crontab_script2.sh`

app_path=`cd $(dirname $0) && pwd`
cd $app_path
php think startcalling