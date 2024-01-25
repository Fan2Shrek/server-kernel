<?php

namespace App;

use Symfony\Component\HttpFoundation\Request;

class ClientRequest extends Request implements \JsonSerializable
{
    public function jsonSerialize(): array
    {
        return [
            'attributes' => json_encode($this->attributes->all()),
            'request' => json_encode($this->request->all()),
            'query' => json_encode($this->query->all()),
            'server' => json_encode($this->server->all()),
            'files' => json_encode($this->files->all()),
            'cookies' => json_encode($this->cookies->all()),
            'headers' => json_encode($this->headers->all()),
        ];
    }
}
