# loginWithTwitter

This REST API provides authentification via twitter for your application. The idea is to keep account management away from you.
Twitter will verifiy your users and you can focus on the interesting things.

## howto to run it in 3 steps

### 1. get code
```
git clone https://github.com/itsmethemojo/loginWithTwitter.git
```
### 2. composer install

```
sudo docker run --rm --interactive --tty --volume $PWD:/app composer install
```

### 3. start local server including redis

```
sudo docker run --name local-redis -d redis ;\
sudo docker build -t local-php . ;\
sudo docker run -td -p 80:8080 --name login-api -v $(pwd):/var/www local-php ;\
echo -e "\n\n   open this url: http://"$(sudo docker inspect -f '{{range .NetworkSettings.Networks}}{{.IPAddress}}{{end}}' login-api)":8080/status\n\n" ;\
sudo docker exec -t login-api php -S 0.0.0.0:8080 index.php

```

## configure the application

1. create config file
```
cp config/login.example.ini config/login.ini
```
2. go to https://apps.twitter.com/ and hit **Create New App** and add the two keys to the new config file
3. also add a list of allowed twitter accounts by id

**TODO** add by name and or empty list for everyone

## available API routes

[documentation](documentation/routes.md)

## howto use the development tools with the container

[documentation](documentation/tools.md)

## howto configure your webserver for it

[documentation](https://www.slimframework.com/docs/start/web-servers.html)
