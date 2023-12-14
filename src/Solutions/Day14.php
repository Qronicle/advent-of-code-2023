<?php

namespace AdventOfCode\Solutions;

use AdventOfCode\Common\Solution\AbstractSolution;

class Day14 extends AbstractSolution
{
    protected array $map = [];
    protected int $width = 0;
    protected int $height = 0;

    protected function solvePart1(): string
    {
        $this->init();
        $this->slideNorth();
        return $this->getTotalWeight();
    }

    protected function solvePart2(): string
    {
        $this->init();
        $history = [];
        $totalCycles = 1000000000;
        for ($i = 0; $i < $totalCycles; $i++) {
            $this->cycle();
            $key = implode('', array_map(fn(array $row) => implode('', $row), $this->map));
            if (isset($history[$key])) {
                $cyclesAfterStart = $totalCycles - $history[$key] - 1;
                $cycleLength = $i - $history[$key];
                $cyclesToDo = $cyclesAfterStart % $cycleLength;
                for ($j = 0; $j < $cyclesToDo; $j++) {
                    $this->cycle();
                }
                break;
            }
            $history[$key] = $i;
        }
        return $this->getTotalWeight();
    }

    protected function cycle(): void
    {
        $this->slideNorth();
        $this->slideWest();
        $this->slideSouth();
        $this->slideEast();
    }

    protected function slideNorth(): void
    {
        foreach ($this->map as $y => $row) {
            foreach ($row as $x => $tile) {
                if ($tile === 'O' && $y > 0) {
                    $targetY = $y;
                    do {
                        if ($this->map[$targetY - 1][$x] !== '.') {
                            break;
                        }
                    } while (--$targetY > 0);
                    if ($targetY < $y) {
                        $this->map[$targetY][$x] = 'O';
                        $this->map[$y][$x] = '.';
                    }
                }
            }
        }
    }

    protected function slideSouth(): void
    {
        for ($y = $this->height - 1; $y >= 0; $y--) {
            for ($x = 0; $x < $this->width; $x++) {
                if ($this->map[$y][$x] === 'O' && $y < $this->height - 1) {
                    $targetY = $y;
                    do {
                        if ($this->map[$targetY + 1][$x] !== '.') {
                            break;
                        }
                    } while (++$targetY < $this->height - 1);
                    if ($targetY > $y) {
                        $this->map[$targetY][$x] = 'O';
                        $this->map[$y][$x] = '.';
                    }
                }
            }
        }
    }

    protected function slideEast(): void
    {
        for ($x = $this->width - 1; $x >= 0; $x--) {
            for ($y = 0; $y < $this->height; $y++) {
                if ($this->map[$y][$x] === 'O' && $x < $this->width - 1) {
                    $targetX = $x;
                    do {
                        if ($this->map[$y][$targetX + 1] !== '.') {
                            break;
                        }
                    } while (++$targetX < $this->width - 1);
                    if ($targetX > $x) {
                        $this->map[$y][$targetX] = 'O';
                        $this->map[$y][$x] = '.';
                    }
                }
            }
        }
    }

    protected function slideWest(): void
    {
        for ($x = 0; $x < $this->width; $x++) {
            for ($y = 0; $y < $this->height; $y++) {
                if ($this->map[$y][$x] === 'O' && $x > 0) {
                    $targetX = $x;
                    do {
                        if ($this->map[$y][$targetX - 1] !== '.') {
                            break;
                        }
                    } while (--$targetX > 0);
                    if ($targetX < $x) {
                        $this->map[$y][$targetX] = 'O';
                        $this->map[$y][$x] = '.';
                    }
                }
            }
        }
    }

    protected function getTotalWeight(): int
    {
        $total = 0;
        $weight = $this->height;
        foreach ($this->map as $row) {
            foreach ($row as $tile) {
                if ($tile === 'O') {
                    $total += $weight;
                }
            }
            $weight--;
        }
        return $total;
    }

    protected function init(): void
    {
        $this->map = $this->getInputMap();
        $this->width = count($this->map[0]);
        $this->height = count($this->map);
    }
}
