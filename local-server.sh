#!/bin/bash

# does composer install within container
task build

# creates container image
task local-server

# startup redis container
docker stop local-redis || true
docker rm local-redis || true
docker run --name local-redis -d redis

#startup login api container
docker stop login-api || true
docker rm login-api || true
docker run -td --name login-api -v $(pwd):/var/www dckrz-${PWD##*/}-local-server
echo -e "\n\n   open this url: http://"$(docker inspect -f '{{range .NetworkSettings.Networks}}{{.IPAddress}}{{end}}' login-api)"/status\n\n"
