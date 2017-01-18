# loginWithTwitter

This REST API provides authentification via twitter for your application. The idea is to keep account management away from you.
Twitter will verifiy your users and you can focus on the interesting things.

## howto to run it in 4 steps

### 1. checkout
```
git clone https://github.com/itsmethemojo/loginWithTwitter.git
```
### 2. start Vagrant Box

```
vagrant up
```
### 3. install Dependencies

```
vagrant ssh
cd /vagrant
composer install
```

### 4. create configuration file

create an ini configuration file **config/twitter.ini**
```
consumerKey = [YOUR_TWITTER_CONSUMER_KEY]
consumerSecret = [YOUR_TWITTER_CONSUMER_SECRET]
lifetime = 2592000
whitelist[] = [FIRST_TWITTER_ACOUNT_ID]
;whitelist[] = [SECOND_TWITTER_ACOUNT_ID]
dbHost = localhost
dbPort = 27017
```

## howto get it working on your server

1. check the **vagrant/default.conf** to see, howto configure a nginx/php-fpm for this
2. check the linked scripts in **vagrant/provision.sh** for the needed software stack

## howto embed it in other applications

TODO 

