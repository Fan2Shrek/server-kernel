<?php

namespace App;

use Symfony\Component\HttpFoundation\Request;

class ServerRequest
{
    private Request $request;

    private \Socket $socket;

    public function __construct(Request $request, \Socket $socket)
    {
        $this->request = $request;
        $this->socket = $socket;
    }

    public function getRequest(): Request
    {
        return $this->request;
    }

    public function getSocket(): \Socket
    {
        return $this->socket;
    }
}
