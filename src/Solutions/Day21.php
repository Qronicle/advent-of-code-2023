<?php

namespace AdventOfCode\Solutions;

use AdventOfCode\Common\Output\ImageOutput;
use AdventOfCode\Common\Solution\AbstractSolution;

class Day21 extends AbstractSolution
{
    protected function solvePart1(): string
    {
        [$map, $w, $h, $startPoint] = $this->createMap();

        return $this->getNumPositions($map, $startPoint, 64);
    }

    protected function solvePart2(): string
    {
        [$map, $w, $h, $startPoint] = $this->createMap();

        // after 65 steps, the + has reached the edges
        // after 65 + 65 steps, the x has reached the corners
        // each + axis field takes 131 steps to reach end, 196 steps to completely fill
        // each diagonal takes
        // because the map size is odd, the odd/even fields switch around:
        //
        //                                  +-----------+-----------+-----------+    12 steps
        //        E                         | 0 1 0 1 0 | 1 0 1 0 1 | 0 1 0     |    1st : 2 to border
        //      E O E                       | 1 0 1 0 1 | 0 1 0 1 0 | 1 0 1 0   |          4 to fill
        //    E O E O E                     | 0 1 0 1 0 | 1 0 1 0 1 | 0 1 0 1 0 |    full: 5 to border
        //      E O E                       | 1 0 1 0 1 | 0 1 0 1 0 | 1 0 1 0   |          7 to fill
        //        E                         | 0 1 0 1 0 | 1 0 1 0 1 | 0 1 0     |
        //                                  +-----------+-----------+-----------+
        //                                  | 1 0 1 0 1 | 0 1 0 1 0 | 1 0       |    corner = 11th step => 2 to go
        //                                  | 0 1 0 1 0 | 1 0 1 0 1 | 0         |           => (12 - (4 + 1)) % 5
        //                                  | 1 0 1 0 1 | 0 1 0 1 0 |           |
        //                                  | 0 1 0 1 0 | 1 0 1 0   |           |    fat corner =>
        //                                  | 1 0 1 0 1 | 0 1 0     |           |
        //                                  +-----------+-----------+-----------+
        //                                  | 0 1 0 1 0 | 1 0       |           |
        //                                  | 1 0 1 0 1 | 0         |           |
        //                                  | 0 1 0 1 0 |           |           |
        //                                  |   0 1 0   |           |           |
        //                                  |     0     |           |           |
        //                                  +-----------+-----------+-----------+

        $numEven = $this->getNumPositions($map, $startPoint, 130);
        $numOdd = $this->getNumPositions($map, $startPoint, 130, 1);

        // we start at the center horizontal axis

        // 26501365 - 65 = 26501300
        // 26501300 / 131 = 202300

        // all fields are full except for the outer 2, which are even fields, so starting on odd times
        $numRight = $this->getNumPositions($map, [0, 65], 130); // 130 because we already took the first step
        $numLeft = $this->getNumPositions($map, [130, 65], 130);
        $numTop = $this->getNumPositions($map, [65, 130], 130);
        $numBottom = $this->getNumPositions($map, [65, 0], 130);
        $numBR = [
            $this->getNumPositions($map, [0, 0], 64), // 26501365 % 131 (-1 for first step)
            $this->getNumPositions($map, [0, 0], 195),
        ];
        $numTR = [
            $this->getNumPositions($map, [0, 130], 64),
            $this->getNumPositions($map, [0, 130], 195),
        ];
        $numTL = [
            $this->getNumPositions($map, [130, 130], 64),
            $this->getNumPositions($map, [130, 130], 195),
        ];
        $numBL = [
            $this->getNumPositions($map, [130, 0], 64),
            $this->getNumPositions($map, [130, 0], 195),
        ];

        $numSloped = array_sum($numTR) + array_sum($numBR) + array_sum($numBL) + array_sum($numTL);

        $sideFieldCount = (26501365 - 65) / 131;
        $oddFieldCount = 1 + $sideFieldCount - 2; // center one is even, outer ones are even but removed because not complete
        $evenFieldCount = $sideFieldCount;
        $numPositions = 0;
        // Add first row
        $numPositions += $oddFieldCount * $numOdd + $evenFieldCount * $numEven + $numLeft + $numRight;
        $d = 0;
        while (++$d) {
            $oddFieldCount--;
            $evenFieldCount--;
            if ($oddFieldCount <= 0 && $evenFieldCount <= 0) {
                // we have reached the top/bottom
                $numPositions += $numTop + $numBottom + $numTR[0] + $numBR[0] + $numBL[0] + $numTL[0];
                break;
            }
            $numInFullFields = ($oddFieldCount * $numOdd + $evenFieldCount * $numEven) * 2;
            $numPositions += $numInFullFields + $numSloped;
        }

        return $numPositions;
    }

    protected function getNumPositions(array $map, array $start, int $steps, int $step = 0): int
    {
        $step %= 2;
        $startStep = $step;
        $dirs = [[0, 1], [0, -1], [1, 0], [-1, 0]];
        $points = [$start];
        $traversed[$start[1]][$start[0]] = $step;
        while ($points && $step++ < $steps) {
            // $this->createImg($map, $points, $traversed, $step);
            $newPoints = [];
            $mod = $step % 2;
            foreach ($points as [$x, $y]) {
                foreach ($dirs as [$dirX, $dirY]) {
                    $newX = $x + $dirX;
                    $newY = $y + $dirY;
                    if (($map[$newY][$newX] ?? null) === '.' && !isset($traversed[$newY][$newX])) {
                        $traversed[$newY][$newX] = $mod;
                        $newPoints[] = [$newX, $newY];
                    }
                }
            }
            $points = $newPoints;
        }
        // $this->createImg($map, [], $traversed, $step, $startStep . '--' . $start[0] . '-' . $start[1] . '--' . $steps);
        //ImageOutput::pngSequenceToGif('var/out/day21', 'day21.gif');
        $sum = 0;
        $endMod = $steps % 2;
        foreach ($traversed as $points) {
            foreach ($points as $pointMod) {
                $sum += $pointMod === $endMod ? 1 : 0;
            }
        }
        return $sum;
    }

    protected function createMap(): array
    {
        // Make le map
        $map = [];
        $start = null;
        $w = null;
        foreach ($this->getInputLines() as $y => $line) {
            $w ??= strlen($line);
            for ($x = 0; $x < $w; $x++) {
                $char = $line[$x];
                if ($line[$x] === 'S') {
                    $start = [$x, $y];
                    $char = '.';
                }
                $map[$y][] = $char;
            }
        }
        $h = count($map);
        return [$map, $w, $h, $start];
    }

    protected function createImg(array $map, array $points, array $traversed, int $step, string $filename = null): void
    {
        $mod = ($step - 1) % 2;
        foreach ($traversed as $y => $traversedPoints) {
            foreach ($traversedPoints as $x => $traversedPoint) {
                if ($filename) {
                    $map[$y][$x] = $traversedPoint === $mod ? 'X' : '.';
                } else {
                    $even = $traversedPoint === 0;
                    $odd = $traversedPoint === 1;
                    if ($odd && $even) {
                        $map[$y][$x] = 'F';
                    } else {
                        $map[$y][$x] = $odd ? 'O' : 'E';
                    }
                }

            }
        }
        foreach ($points as [$x, $y]) {
            $map[$y][$x] = 'P';
        }
        $filename ??= str_pad($step, 10, '0', STR_PAD_LEFT);
        ImageOutput::map($map, "var/out/day21/$filename.png", 1, [
            '.' => [230, 230, 230],
            '#' => [80, 80, 80],
            'X' => [200, 180, 180],
            'P' => [200, 200, 0],
            'O' => $step % 2 === 1 ? [255, 50, 50] : [255, 200, 200],
            'E' => $step % 2 === 0 ? [50, 50, 255] : [200, 200, 255],
            'F' => [255, 50, 255],
        ]);
    }
}