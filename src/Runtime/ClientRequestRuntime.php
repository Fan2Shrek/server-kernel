<?php

namespace App\Runtime;

use Symfony\Component\Runtime\ResolverInterface;
use Symfony\Component\Runtime\RunnerInterface;
use Symfony\Component\Runtime\SymfonyRuntime;
use App\ClientRequest;
use Symfony\Component\HttpFoundation\Request;

class ClientRequestRuntime extends SymfonyRuntime
{
    public function getResolver(callable $callable, \ReflectionFunction $reflector = null): ResolverInterface
    {
        return parent::getResolver($callable, $reflector);
    }

    public function getRunner(?object $application): RunnerInterface
    {
        if ($application instanceof ClientRequest) {
            $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
            socket_connect($socket, '127.0.0.1', $_ENV['KERNEL_PORT']);
            return new ClientRequestRunner($socket, ClientRequest::createFromGlobals());
        }

        return parent::getRunner($application);
    }
}
