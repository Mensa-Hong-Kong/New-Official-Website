#! /bin/bash

SCRIPT_DIR=$(realpath "$(dirname "$0")")

docker kill new-official-website-mysql-1 &> /dev/null
docker container rm new-official-website-mysql-1 &> /dev/null
docker compose up -d

isRunning=""
echo "waiting for container to be healthy"
while [ -z "$isRunning" ]
do
    isRunning=`docker ps --filter health=healthy --filter name=new-official-website-mysql-1 -q`

    if [ -z "$isRunning" ]
    then
        printf ".%.0s" $(seq 1 1)
        sleep 1
    fi
done

cd "$SCRIPT_DIR/ansible"
ansible-playbook -i environments/local/hosts.yml playbooks/phpunit.yml

cd $SCRIPT_DIR
php artisan migrate --seed
