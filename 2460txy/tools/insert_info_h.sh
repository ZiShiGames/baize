#!/bin/sh
## ever hours insert platforms data info in to base
requery=`sudo ifconfig -a|grep inet|grep -v 127.0.0.1|grep -v inet6|awk '{print $2}'|tr -d "addr:";`

curl "http://$requery/index.php/Admin_public_api/every_hour_date/0/`date +%H -d '+1 hours'`";
