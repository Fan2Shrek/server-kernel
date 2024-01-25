<?php

namespace App\Runtime;

use Symfony\Component\Runtime\ResolverInterface;
use App\ClientRequest;

class ClientRequestResolver implements ResolverInterface
{
    public function resolve(): array
    {
        return [
            fn () => new ClientRequest(),
            function () {
                return new \ReflectionFunction(function () {
                    return new ClientRequest();
                });
            },
        ];
    }
}
