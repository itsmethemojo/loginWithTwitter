# loginWithTwitter
This application provides authentification via twitter for your application. The idea is to keep account management away from you.
Twitter will verifiy your users and you can focus on the interesting things.

## howto use it

### login only for the session

```php
require __DIR__ .'/../vendor/autoload.php';

$twitter = new Itsmethemojo\Authentification\Twitter();
var_dump($twitter->getLoginUser());
```

### authorize cookie token for login

```php
require __DIR__ .'/../vendor/autoload.php';

$twitter = new Itsmethemojo\Authentification\TwitterExtended();
var_dump($twitter->getLoginUser());
```

## coming features

* black and whitelists for twitter handles

## installation

### config files

the different software stacks are configured with ini files placed in the **/config** folder

#### login-mysql.ini
```
host = localhost
username = root
password = root
databaseName = login
```

#### redis.ini
```
host = localhost
```

#### twitter.ini
```
consumerKey = ENTER_KEY_HERE
consumerSecret = ENTER_SECRET_HERE
lifetime = 2592000
```

### mysql table

for the token method a mysql table is needed

```sql
CREATE TABLE `tokens` (
  `token` varchar(100) CHARACTER SET utf8 NOT NULL,
  `user_id` varchar(100) CHARACTER SET utf8 NOT NULL,
  `expires` bigint(20) NOT NULL,
  PRIMARY KEY (`token`)
) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8; 
```

