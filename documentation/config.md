[back to README](../README.md)

## create the config file
you can copy the template
```
cp config/login.example.ini config/login.ini
```

## get Twitter Application Keys
go to https://apps.twitter.com/ and hit **Create New App** and add the two keys to the new config file

## add the Redis configuration
to get the host ip from the local redis simply run this
```
docker inspect -f '{{range .NetworkSettings.Networks}}{{.IPAddress}}{{end}}' local-redis
```

## restrict the allowed Twitter users
also add a list of allowed twitter accounts by id

**TODO** add by name and or empty list for everyone
