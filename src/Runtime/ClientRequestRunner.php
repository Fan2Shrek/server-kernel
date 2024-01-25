<?php

namespace App\Runtime;

use Symfony\Component\Runtime\RunnerInterface;
use Symfony\Component\HttpFoundation\Request;

class ClientRequestRunner implements RunnerInterface
{
    public function __construct(
        private readonly \Socket $socket,
        private readonly Request $request,
    ) {
    }

    /**
     * @todo Maybe externalise this logic to a dedicated class
     */
    public function run(): int
    {
        $message = serialize($this->request);
        socket_write($this->socket, $message, strlen($message));

        $response = socket_read($this->socket, 1048576);
        $response = unserialize($response);
        socket_close($this->socket);

        $response->send();

        return 0;
    }
}
