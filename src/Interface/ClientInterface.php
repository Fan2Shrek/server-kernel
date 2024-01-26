<?php

namespace App\Interface;

use App\ClientRequest;
use Symfony\Component\HttpFoundation\Response;

interface ClientInterface
{
    public function sendRequest(ClientRequest $request): void;

    public function receiveResponse(): Response;

    public function handleResponse(Response $response): void;
}
