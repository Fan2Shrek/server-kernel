<?php

namespace App\Clock;

use Symfony\Component\Clock\ClockInterface;

interface RunningClockInterface extends ClockInterface
{
    public function start(): void;

    public function stop(): void;

    public function isRunning(): bool;

    public function getRunningTime(): float;
}
