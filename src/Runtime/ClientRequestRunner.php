<?php

namespace App\Runtime;

use Symfony\Component\Runtime\RunnerInterface;
use Symfony\Component\HttpFoundation\Request;
use App\Interface\ClientInterface;
use Symfony\Component\HttpKernel\TerminableInterface;

class ClientRequestRunner implements RunnerInterface
{
    public function __construct(
        private readonly ClientInterface $client,
        private readonly Request $request,
    ) {
    }

    public function run(): int
    {
        $this->client->sendRequest($this->request);
        $response = $this->client->receiveResponse();

        $this->client->handleResponse($response);

        if ($this->client instanceof TerminableInterface) {
            $this->client->terminate($this->request, $response);
        }

        return 0;
    }
}
