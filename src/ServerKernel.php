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

    private \Socket $serverSocket;

    public function boot(): void
    {
        parent::boot();
        $this->innerClock = new RunningClock(new NativeClock());
    }

    public function start(): void
    {
        $this->stop = false;

        $this->configureSocket();
        $this->run();
    }

    private function run(): void
    {
        $this->innerClock->start();

        while (!$this->stop) {
            $this->refreshRequestQueue();
            if (!empty($this->requestQueue)) {
                $this->handleCurrentQueue();
            }
        }

        $this->innerClock->stop();
        $this->stop();
        $this->shutdown();
    }

    public function stop(): void
    {
        socket_close($this->serverSocket);
    }

    private function refreshRequestQueue(): void
    {
        $clientSocket = socket_accept($this->serverSocket);
        $request = socket_read($clientSocket, 1024);

        if ($request instanceof Request) {
            $this->requestQueue[] = $request;
        }
    }

    private function configureSocket(): void
    {
        $this->serverSocket = \socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        socket_bind($this->serverSocket, '127.0.0.1', 8000);
        socket_listen($this->serverSocket);
    }

    private function handleCurrentQueue(): void
    {
        foreach ($this->requestQueue as $request) {
            $this->handleRequest($request);
        }
    }

    public function handleRequest(Request $request): void
    {
        $response = $this->handle($request);
        $this->handleResponse($response);
    }

    public function handleResponse(Response $response): void
    {
        socket_write($this->serverSocket, $response, strlen($response));
    }
}
