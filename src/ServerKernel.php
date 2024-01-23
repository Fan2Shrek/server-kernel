<?php

namespace App;

use App\Clock\RunningClock;
use App\Clock\RunningClockInterface;
use Symfony\Component\Clock\NativeClock;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * @author Pierre Ambroise<pierre27.ambroise@gmail.com>
 */
final class ServerKernel extends Kernel
{
    private bool $stop = false;

    private array $requestQueue = [];

    private RunningClockInterface $innerClock;

    public function boot(): void
    {
        parent::boot();
        $this->innerClock = new RunningClock(new NativeClock());
    }

    public function start(): void
    {
        $this->stop = false;
        $this->innerClock->start();

        while (!$this->stop) {
            if (!empty($this->requestQueue)) {
                $this->handleCurrentQueue();
            }
        }

        $this->innerClock->stop();
    }

    private function handleCurrentQueue(): void
    {
        foreach ($this->requestQueue as $request) {
            $this->handleReuqest($request);
        }
    }

    public function handleReuqest(Request $request): void
    {
        $response = $this->handle($request);
        $this->handleResponse($response);
    }

    public function handleResponse(Response $response): void
    {
        $response->send();
    }
}
