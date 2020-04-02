#!/bin/sh
# this script is used for clean up logs that out of date
echo "clean start"
date
cd /var/html/2460/application/cache && time find . -type f -mtime +1 -exec rm -f {} \;
cd /var/html/2460/application/logs && time find . -type f -mtime +5 -exec rm -f {} \;
date
echo "clean end"
