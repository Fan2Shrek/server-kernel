<?php

namespace App\Runtime;

use App\Client;
use Symfony\Component\Runtime\ResolverInterface;
use Symfony\Component\Runtime\RunnerInterface;
use Symfony\Component\Runtime\SymfonyRuntime;
use App\ClientRequest;
use Symfony\Component\HttpFoundation\Request;

class ClientRequestRuntime extends SymfonyRuntime
{
    public function getArgument(\ReflectionParameter $parameter, ?string $type): mixed
    {
        return match ($parameter->getName()) {
            'host' => $this->getEnvOrThrow('KERNEL_HOST'),
            'port' => $this->getEnvOrThrow('KERNEL_PORT'),
            default =>  parent::getArgument($parameter, $type),
        };
    }

    private function getEnvOrThrow(string $key): string
    {
        $value = $_ENV[$key] ?? null;

        if ($value === null) {
            throw new \RuntimeException(sprintf('Missing env variable "%s"', $key));
        }

        return $value;
    }

    public function getRunner(?object $application): RunnerInterface
    {
        if ($application instanceof Client) {
            return new ClientRequestRunner($application, ClientRequest::createFromGlobals());
        }

        return parent::getRunner($application);
    }
}
