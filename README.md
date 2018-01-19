# loginWithTwitter

This REST API provides authentification via twitter for your application. The idea is to keep account management away from you.
Twitter will verifiy your users and you can focus on the interesting things.

## howto to run it local in 2 minutes

```
docker run --rm --interactive --tty --volume $PWD:/app composer install ;\
docker run --name local-redis -d redis ;\
docker build -t login-api . ;\
docker run -td -p 80:8080 --name login-api -v $(pwd):/var/www login-api ;\
echo -e "\n\n   open this url: http://"$(docker inspect -f '{{range .NetworkSettings.Networks}}{{.IPAddress}}{{end}}' login-api)":8080/status\n\n" ;\
docker exec -t login-api php -S 0.0.0.0:8080 index.php

```

## what's next?

### configure the application

[more...](documentation/config.md)

### available API routes

[more...](documentation/routes.md)

### howto use the development tools with the container

[more...](documentation/tools.md)

### howto configure your webserver for it

[more...](https://www.slimframework.com/docs/start/web-servers.html)
