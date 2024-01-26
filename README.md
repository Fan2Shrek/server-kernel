# Server Kernel

To be honest, I don't know what I'm doing. I'm just trying to make a server kernel for a symfony project. I'm not sure if I'm doing it right, but I'm trying my best :). I'm using [PHP Socket](https://www.php.net/manual/fr/book.sockets.php) to do this.

Don't use this in production, it's just a test it could be easy to attack.

## How to use it

First add those variables in your `.env` file

```env
KERNEL_HOST=127.0.0.1
KERNEL_PORT=8000
```

Next, run the server you can set the port with the option `--port`

```bash
bin/console start:kernel
```

Now, you need to modify the `index.php` file in the public folder of your symfony project.

```php
<?php

use App\Client;

require_once dirname(__DIR__).'/vendor/autoload_runtime.php';

return fn (string $host, int $port) => new Client($host, $port);
```

Lasty you need to update the `composer.json` file to register the runtime class

```json
"extra": {
    "runtime": {
        "class": "App\\Runtime\\ClientRequestRuntime"
    }
}
```

To apply the changes, you need to run the command `composer install` or `composer update`


Now you should be able to run your symfony project with the server kernel.
