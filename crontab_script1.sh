#!/bin/sh

# use example:
# crontab add: `* * * * * /var/www/html/yyb/crontab_script1.sh`

app_path=`cd $(dirname $0) && pwd`
cd $app_path
php think close:appoint