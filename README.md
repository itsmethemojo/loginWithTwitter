# loginWithTwitter

This REST API provides authentification via twitter for your application. The idea is to keep account management away from you.
Twitter will verifiy your users and you can focus on the interesting things.

## required software

* docker
* [go-task](https://taskfile.org/#/installation?id=install-script)

## howto to run it locally with a single command

```
./local-server.sh
```
within the console output the address of the local login api will be printed e.g.

```
open this url: http://172.17.0.8:8080/status
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
