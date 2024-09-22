#!/bin/bash

for filename in $GITHUB_WORKSPACE/docker/mysql/schemas/*.sql; do
    mysql -h 127.0.0.1 -u root -p$MYSQL_ROOT_PASSWORD mensa < $filename
done
