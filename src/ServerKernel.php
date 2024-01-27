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

    private int $port;

    public function __construct(string $environment, bool $debug, int $port)
    {
        parent::__construct($environment, $debug);
        $this->port = $port;
    }

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

    /**
     * @todo we should use a loop here
     * @todo we should take advantage of the clock
     */
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
        $this->shutdown();
    }

    private function refreshRequestQueue(): void
    {
        $clientSocket = socket_accept($this->serverSocket);
        $request = socket_read($clientSocket, 65536);

        $realRequest = unserialize($request);
        if ($realRequest instanceof Request) {
            /** @todo we should no use array here */
            $this->requestQueue[] = ['request' => $realRequest, 'socket' => $clientSocket];
        }
    }

    private function configureSocket(): void
    {
        $this->serverSocket = \socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        socket_bind($this->serverSocket, '0.0.0.0', $this->port);
        socket_listen($this->serverSocket);
    }

    private function handleCurrentQueue(): void
    {
        foreach ($this->requestQueue as $request) {
            $this->handleRequest($request);
        }
    }

    public function handleRequest(array $request): void
    {
        $response = $this->handle($request['request']);

        $this->handleResponse($response, $request['socket']);
        $this->removeRequest();
    }

    public function removeRequest(): void
    {
        array_shift($this->requestQueue);
    }

    public function handle(Request $request, int $type = HttpKernelInterface::MAIN_REQUEST, bool $catch = true): Response
    {
        return $this->getHttpKernel()->handle($request, $type, $catch);
    }

    public function handleResponse(Response $response, \Socket $clientSocket): void
    {
        $message = serialize($response);
        socket_write($clientSocket, $message, strlen($message));
    }
}
