# Cache for PHP dotenv
A simple cached loader for [PHP dotenv](https://github.com/vlucas/phpdotenv).

## Usage

First, install [Composer](http://getcomposer.org/).

```bash
curl -sS https://getcomposer.org/installer | php
```

Next, add phpdotenvcache:

```
php composer.phar require claas/phpdotenvcache
```

Then, include Composer's autoloader:

```php
require 'vendor/autoload.php';
```

Finally, you can use the PHP dotenv cache:

```php
$dotenv = new Dotenv\CachedDotenv();
$dotenv->load(__DIR__);
// Alternatively, only load to $_ENV or $_SERVER globals:
// $dotenv->loadToEnv(__DIR__);
// $dotenv->loadToServer(__DIR__); 
```
