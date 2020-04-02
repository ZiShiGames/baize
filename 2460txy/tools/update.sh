#!/bin/sh
STATUS=`cat ../img/git_update`;
if [ $STATUS -eq 1 ]; then
    git pull
    echo 0 > ../img/git_update
fi
