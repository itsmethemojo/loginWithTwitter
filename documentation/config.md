[back to README](../README.md)

## create the config file
you can copy the template
```
cp config/login.ini.example config/login.ini
```

## get Twitter Application Keys
Go to https://apps.twitter.com/ and hit **Create New App** and add the two keys to the new config file.

Also define a valid callback url.

You might add this line in your /etc/hosts
`
172.17.0.8 login.api
`
then you only have to add **http://login.api/login** in your twitter app as callback url. If the IP of the container changes just edit your hosts file.

## add the Redis configuration
to get the host ip from the local redis simply run this
```
docker inspect -f '{{range .NetworkSettings.Networks}}{{.IPAddress}}{{end}}' local-redis
```

## restrict the allowed Twitter users
also add a list of allowed twitter accounts by id

**TODO** add by name and or empty list for everyone
