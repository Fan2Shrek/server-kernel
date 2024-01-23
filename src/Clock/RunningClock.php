<?php

namespace App\Clock;

use Symfony\Component\Clock\NativeClock;

class RunningClock implements RunningClockInterface
{
    private bool $isRunning = false;

    private \DateTimeInterface $startTime;

    private \DateTimeInterface $endTime;

    public function __construct(private NativeClock $innerClock)
    {
    }

    public function start(): void
    {
        if (!$this->isRunning) {
            $this->isRunning = true;
            $this->startTime = $this->now();
        }
    }

    public function stop(): void
    {
        if ($this->isRunning) {
            $this->endTime = $this->now();
            $this->isRunning = false;
        }
    }

    public function isRunning(): bool
    {
        return $this->isRunning;
    }

    public function getRunningTime(): float
    {
        if ($this->isRunning) {
            throw new \LogicException('The clock is still running.');
        }

        return $this->endTime->getTimestamp() - $this->startTime->getTimestamp();
    }

    public function sleep(float|int $seconds): void
    {
        $this->innerClock->sleep($seconds);
    }

    public function withTimeZone(\DateTimeZone|string $timezone): static
    {
        return new self($this->innerClock->withTimeZone($timezone));
    }

    public function now(): \DateTimeImmutable
    {
        return $this->innerClock->now();
    }
}
