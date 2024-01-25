<?php

namespace App\Runtime;

use Symfony\Component\Runtime\RunnerInterface;
use Symfony\Component\HttpFoundation\Request;

class ClientRequestRunner implements RunnerInterface
{
    public function __construct(
        private readonly \Socket $socket,
        private readonly Request $request
    ) {
    }

    public function run(): int
    {
        socket_write($this->socket, $this->request->getContent());

        $response = socket_read($this->socket, 1024);

        // $response->send();

        return 0;
    }
}
