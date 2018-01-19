#!/bin/bash

docker exec -t login-api bash -c 'cd ..; vendor/bin/phpcbf --standard=PSR2 src public; vendor/bin/phpcs --standard=PSR2 src public'
