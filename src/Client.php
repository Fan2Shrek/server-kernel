<?php

namespace App;

use App\Interface\ClientInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\TerminableInterface;
use Symfony\Component\HttpFoundation\Request;

class Client implements ClientInterface, TerminableInterface
{
    private \Socket $socket;

    public function __construct(private readonly string $host, private readonly int $port)
    {
        $this->socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        socket_connect($this->socket, $this->host, $this->port);
    }

    public function sendRequest(ClientRequest $request): void
    {
        $message = serialize($request);
        socket_write($this->socket, $message, strlen($message));
    }

    public function receiveResponse(): Response
    {
        $response = '';

        while ($data = socket_read($this->socket, 2 ** 6)) {
            $response .= $data;
        }

        $response = unserialize($response);

        if (!$response instanceof Response) {
            throw new \RuntimeException('Invalid response');
        }

        return $response;
    }

    public function handleResponse(Response $response): void
    {
        $message = serialize($response);

        $response->send();
    }

    public function terminate(Request $request, Response $response): void
    {
        socket_close($this->socket);
    }
}
