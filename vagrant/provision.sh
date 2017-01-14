#!/bin/bash


sudo apt-get install -y curl

#TODO use tag instead of develop

bash <(curl -s https://raw.githubusercontent.com/itsmethemojo/utils/develop/vagrant/provison/php7/webserver.sh)
bash <(curl -s https://raw.githubusercontent.com/itsmethemojo/utils/develop/vagrant/provison/php7/mongodb.sh)
