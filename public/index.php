<?php


use App\Client;

require_once dirname(__DIR__).'/vendor/autoload_runtime.php';

return fn (string $host, int $port) => new Client($host, $port);
