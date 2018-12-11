#!/bin/sh

# use example:
# crontab add: `0,30 8-23 * * * /var/www/html/yyb/crontab_script3.sh`

app_path=`cd $(dirname $0) && pwd`
cd $app_path
php think close:video