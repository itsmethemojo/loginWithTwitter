## use PHP Codesniffer

when the container is running open another bash so you can run the tasks in the container
```
sudo docker exec -it login-api
cd /var/www/
vendor/bin/phpcs --standard=PSR2 src public
vendor/bin/phpcbf --standard=PSR2 src public
```

## use Composer
you can easily do composer actions with the composer container like this
```
sudo docker run --rm --interactive --tty --volume $PWD:/app composer update slim/slim
sudo docker run --rm --interactive --tty --volume $PWD:/app composer require whatever
```
