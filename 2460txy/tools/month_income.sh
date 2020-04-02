#/bin/bash
requery=`sudo ifconfig -a|grep inet|grep -v 127.0.0.1|grep -v inet6|awk '{print $2}'|tr -d "addr:";`
for var in `curl 'http://$requery/index.php/admin_public_api/month_data_insert'`; do  curl $var; done;
