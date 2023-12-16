<?php

namespace AdventOfCode\Solutions;

use AdventOfCode\Common\Solution\AbstractSolution;

class Day16 extends AbstractSolution
{
    protected function solvePart1(): string
    {
        $map = $this->getInputMap();
        return $this->getNumEnergized($map, [-1, 0, 1, 0]);
    }

    protected function solvePart2(): string
    {
        $map = $this->getInputMap();
        $w = count($map[0]);
        $h = count($map);
        $max = 0;
        for ($x = 0; $x < $w; $x++) {
            $max = max($max, $this->getNumEnergized($map, [$x, -1, 0, 1]));
            $max = max($max, $this->getNumEnergized($map, [$x, $h, 0, -1]));
        }
        for ($y = 0; $y < $h; $y++) {
            $max = max($max, $this->getNumEnergized($map, [-1, $y, 1, 0]));
            $max = max($max, $this->getNumEnergized($map, [$w, $y, -1, 0]));
        }
        return $max;
    }

    protected function getNumEnergized(array $map, array $startPoint): int
    {
        $visited = [];
        $active = [$startPoint];
        $step = 0;
        while ($active) {
            $newActive = [];
            foreach ($active as [$x, $y, $dirX, $dirY]) {
                if (isset($visited[$x][$y][$dirX][$dirY])) {
                    continue;
                }
                if ($step) {
                    $visited[$x][$y][$dirX][$dirY] = true;
                }
                $newX = $x + $dirX;
                $newY = $y + $dirY;
                $tile = $map[$newY][$newX] ?? null;
                if ($tile === null) {
                    continue;
                }
                // get new $dirs
                switch ($tile) {
                    case '.':
                        $newActive[] = [$newX, $newY, $dirX, $dirY];
                        break;
                    case '/':
                        if ($dirX === 1) {
                            $newActive[] = [$newX, $newY, 0, -1];
                        } elseif ($dirX === -1) {
                            $newActive[] = [$newX, $newY, 0, 1];
                        } elseif ($dirY === -1) {
                            $newActive[] = [$newX, $newY, 1, 0];
                        } else {
                            $newActive[] = [$newX, $newY, -1, 0];
                        }
                        break;
                    case '\\':
                        if ($dirX === 1) {
                            $newActive[] = [$newX, $newY, 0, 1];
                        } elseif ($dirX === -1) {
                            $newActive[] = [$newX, $newY, 0, -1];
                        } elseif ($dirY === -1) {
                            $newActive[] = [$newX, $newY, -1, 0];
                        } else {
                            $newActive[] = [$newX, $newY, 1, 0];
                        }
                        break;
                    case '|':
                        if ($dirX !== 0) {
                            $newActive[] = [$newX, $newY, 0, 1];
                            $newActive[] = [$newX, $newY, 0, -1];
                        } else {
                            $newActive[] = [$newX, $newY, $dirX, $dirY];
                        }
                        break;
                    case '-':
                        if ($dirY !== 0) {
                            $newActive[] = [$newX, $newY, -1, 0];
                            $newActive[] = [$newX, $newY, 1, 0];
                        } else {
                            $newActive[] = [$newX, $newY, $dirX, $dirY];
                        }
                        break;
                }
            }
            $active = $newActive;
            $step++;
        }
        return array_reduce($visited, fn(int $total, array $row) => $total += count($row), 0);
    }
}
