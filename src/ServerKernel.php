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

    private bool $sleep = false;

    private \DateTimeImmutable $lastRequest;

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

    private function run(): void
    {
        $this->innerClock->start();
        $this->lastRequest = new \DateTimeImmutable();

        while (!$this->stop) {
            $this->doRun();
        }

        $this->innerClock->stop();
        $this->stop();
        $this->shutdown();
    }

    private function doRun(): void
    {
        if (!$this->sleep && $this->innerClock->now()->getTimestamp() - $this->lastRequest->getTimestamp() > 10) {
            $this->enterSleepMode();
        }

        $this->refreshRequestQueue();
        if (!empty($this->requestQueue)) {
            if ($this->sleep) {
                $this->recoverSleepMode();
            }

            $this->handleCurrentQueue();
        }
    }

    public function stop(): void
    {
        socket_close($this->serverSocket);
        $this->shutdown();
    }

    private function refreshRequestQueue(): void
    {
        $clientSocket = socket_accept($this->serverSocket);
        if (false === $clientSocket) {
            return;
        }

        $request = socket_read($clientSocket, 2 ** 17);

        try {
            $realRequest = unserialize($request);
        } catch (\Exception $e) {
            /**
             * @todo we should still send a response to the client
             * or at least handle the exception
             */
            return;
        }

        if ($realRequest instanceof Request) {
            $this->lastRequest = new \DateTimeImmutable();
            $this->requestQueue[] = new ServerRequest($realRequest, $clientSocket);
        }
    }

    private function configureSocket(): void
    {
        $this->serverSocket = \socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        socket_bind($this->serverSocket, '0.0.0.0', $this->port);
        socket_listen($this->serverSocket);
        // Allow *asynchronous* mode
        // @see https://www.php.net/manual/en/function.socket-set-nonblock.php
        socket_set_nonblock($this->serverSocket);
    }

    private function handleCurrentQueue(): void
    {
        foreach ($this->requestQueue as $request) {
            $this->handleRequest($request);
        }
    }

    public function handleRequest(ServerRequest $request): void
    {
        $response = $this->handle($request->getRequest());

        $this->handleResponse($response, $request->getSocket());
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

    /**
     * @todo we can try to do some things like rebuild container or something else
     */
    private function enterSleepMode(): void
    {
        $this->innerClock->sleep(2);
        $this->sleep = true;
    }

    private function recoverSleepMode(): void
    {
        $this->sleep = false;
    }
}
