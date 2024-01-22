<?php

namespace App;

/**
 * @author Pierre Ambroise<pierre27.ambroise@gmail.com>
 */
final class ServerKernel extends Kernel
{
    private bool $stop = false;

    private array $requestQueue = [];

    public function start(): void
    {
        while (!$this->stop) {
            if (!empty($this->requestQueue)) {
                $this->handleCurrentQueue();
            }
        }
    }

    private function handleCurrentQueue(): void
    {
        foreach ($this->requestQueue as $request) {
            $this->handle($request);
        }
    }
}
