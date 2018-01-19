[back to README](../README.md)

## use PHP Codesniffer

when the container is running open another bash so you can run the tasks in the container
```
docker exec -t login-api bash -c 'cd ..; vendor/bin/phpcbf --standard=PSR2 src public; vendor/bin/phpcs --standard=PSR2 src public'
```

## use phpunit

when the container is running open another bash so you can run the tasks in the container
```
docker exec -t login-api bash -c 'cd ..; vendor/bin/phpunit .'
```

## use Composer
you can easily do composer actions with the composer container like this
```
docker run --rm --interactive --tty --volume $PWD:/app composer update slim/slim
docker run --rm --interactive --tty --volume $PWD:/app composer require whatever
```


## clean up container
to stop and remove the server container again run
```
docker stop login-api ;\
docker rm login-api
```
