<?php

namespace AdventOfCode\Common\Solution;

class SolutionDto
{
    private float $startTime;
    private float $startMemory;
    public float $duration;
    public float $memory;
    public int|string $result;

    public function __construct()
    {
        $this->start();
    }

    public function start(): void
    {
        $this->startTime = microtime(true);
        $this->startMemory = memory_get_usage();
    }

    public function solve(string|int $result): static
    {
        $endTime = microtime(true);
        $this->duration = $endTime - $this->startTime;
        $this->memory = (memory_get_peak_usage() - $this->startMemory) / (1024 * 1024);
        $this->result = $result;
        return $this;
    }
}
