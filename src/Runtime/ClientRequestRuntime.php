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
        $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        if ($application instanceof ClientRequest) {
            return new ClientRequestRunner($socket, Request::createFromGlobals());
        }

        return parent::getRunner($application);
    }
}
