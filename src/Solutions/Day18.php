<?php

namespace AdventOfCode\Solutions;

use AdventOfCode\Common\Solution\AbstractSolution;

class Day18 extends AbstractSolution
{
    protected function solvePart1(array $lines = null): string
    {
        $lines ??= $this->getInputLines();

        // Create full map

        $map = [];
        $x = 0;
        $y = 0;
        $min = [0, 0];
        $max = [0, 0];
        $dirs = ['U' => [0, -1], 'R' => [1, 0], 'D' => [0, 1], 'L' => [-1, 0]];
        foreach ($lines as $line) {
            preg_match('/([A-Z]) (\d+)/', $line, $matches);
            [, $dir, $length] = $matches;
            for ($i = 0; $i < $length; $i++) {
                $x += $dirs[$dir][0];
                $y += $dirs[$dir][1];
                $map[$y][$x] = '#';
            }
            $max[0] = max($max[0], $x);
            $max[1] = max($max[1], $y);
            $min[0] = min($min[0], $x);
            $min[1] = min($min[1], $y);
        }
        $map = MapUtils::createCompleteMap($map, ['l' => $min[0], 'r' => $max[0], 't' => $min[1], 'b' => $max[1]]);

        // Fill lava lake

        // Figure out were we start filling our lava lake
        $lava = [];
        foreach ($map as $y => $row) {
            if ($row[$min[0]] === '#' && $row[$min[0] + 1] === ' ') {
                $lava[] = [$min[0] + 1, $y];
                $map[$y][$min[0] + 1] = '#';
                break;
            }
        }
        // Le fill
        while ($lava) {
            $newLava = [];
            foreach ($lava as $point) {
                foreach ($dirs as $dir) {
                    $x = $point[0] + $dir[0];
                    $y = $point[1] + $dir[1];
                    if (($map[$y][$x] ?? false) === ' ') {
                        $map[$y][$x] = '#';
                        $newLava[] = [$x, $y];
                    }
                }
            }
            $lava = $newLava;
        }

        // Calculate size

        $size = 0;
        foreach ($map as $row) {
            foreach ($row as $tile) {
                $size += $tile === '#' ? 1 : 0;
            }
        }
        return $size;
    }

    protected function solvePart2(): string
    {
        $dirs = [[1, 0], [0, 1], [-1, 0], [0, -1]];

        $points = [];
        $x = 0;
        $y = 0;
        $perimeter = 0;
        foreach ($this->getInputLines() as $line) {
            // Convert line to point
            $len = hexdec(substr($line, -7, 5));
            $dir = substr($line, -2, 1);
            $x += $dirs[$dir][0] * $len;
            $y += $dirs[$dir][1] * $len;
            $points[] = [$x, $y];
            $perimeter += $len;
        }
        $numPoints = count($points);
        $points[] = $points[0];
        $area = 0;
        for ($i = 0; $i < $numPoints; $i++) {
            $area += $points[$i][0] * $points[$i + 1][1] - $points[$i][1] * $points[$i + 1][0];
        }
        return $area / 2 + $perimeter / 2 + 1;
    }
}
