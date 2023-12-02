<?php

namespace AdventOfCode\Solutions;

use AdventOfCode\Common\Solution\AbstractSolution;

class Day02 extends AbstractSolution
{
    protected function solvePart1(): string
    {
        $bag = ['red' => 12, 'green' => 13, 'blue' => 14];
        $indexSum = 0;
        foreach ($this->getInputLines() as $line) {
            $game = new CubeGame($line);
            if ($game->isPossible($bag)) {
                $indexSum += $game->getIndex();
            }
        }
        return $indexSum;
    }

    protected function solvePart2(): string
    {
        $powerSum = 0;
        foreach ($this->getInputLines() as $line) {
            $game = new CubeGame($line);
            $powerSum += $game->getPower();
        }
        return $powerSum;
    }
}

class CubeGame
{
    protected int $index;
    protected array $throws;

    public function __construct(string $def)
    {
        [$gameDef, $resultsDef] = explode(': ', $def);
        $this->index = array_last(explode(' ', $gameDef));
        foreach (explode('; ', $resultsDef) as $i => $throw) {
            foreach (explode(', ', $throw) as $cubeAmount) {
                [$amount, $color] = explode(' ', $cubeAmount);
                $this->throws[$i][$color] = $amount;
            }
        }
    }

    public function isPossible(array $bag): bool
    {
        foreach ($this->throws as $throw) {
            foreach ($throw as $color => $amount) {
                if (($bag[$color] ?? 0) < $amount) {
                    return false;
                }
            }
        }
        return true;
    }

    public function getPower(): int
    {
        $mins = [];
        foreach ($this->throws as $throw) {
            foreach ($throw as $color => $amount) {
                $mins[$color] = max($mins[$color] ?? 0, $amount);
            }
        }
        return array_product($mins);
    }

    public function getIndex(): int
    {
        return $this->index;
    }
}
