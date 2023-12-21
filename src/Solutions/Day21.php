<?php

namespace AdventOfCode\Solutions;

use AdventOfCode\Common\Output\ImageOutput;
use AdventOfCode\Common\Solution\AbstractSolution;

class Day21 extends AbstractSolution
{
    protected function solvePart1(): string
    {
        // Make le map
        $map = [];
        $points = [];
        $traversed = [];
        foreach ($this->getInputLines() as $y => $line) {
            $w ??= strlen($line);
            for ($x = 0; $x < $w; $x++) {
                $char = $line[$x];
                if ($line[$x] === 'S') {
                    $points[] = [$x, $y];
                    $char = '.';
                    $traversed[$y][$x] = [true, false];
                }
                $map[$y][] = $char;
            }
        }
        // Le traverse!
        $step = 0;
        $steps = 64;
        $dirs = [[0, 1], [0, -1], [1, 0], [-1, 0]];
        while ($points && $step++ < $steps) {
            $this->createImg($map, $points, $traversed, $step);
            $newPoints = [];
            $mod = $step % 2;
            foreach ($points as [$x, $y]) {
                foreach ($dirs as [$dirX, $dirY]) {
                    $newX = $x + $dirX;
                    $newY = $y + $dirY;
                    if (($map[$newY][$newX] ?? null) === '.' && empty($traversed[$newY][$newX][$mod])) {
                        $traversed[$newY][$newX][$mod] = true;
                        $newPoints[] = [$newX, $newY];
                    }
                }
            }
            $points = $newPoints;
        }
        $this->createImg($map, [], $traversed, $step);
        ImageOutput::pngSequenceToGif('var/out/day21', 'day21.gif');
        $sum = 0;
        $endMod = $steps % 2;
        foreach ($traversed as $points) {
            foreach ($points as $point) {
                $sum += empty($point[$endMod]) ? 0 : 1;
            }
        }
        return $sum;
    }

    protected function solvePart2(): string
    {
        // Make le map
        $map = [];
        $points = [];
        $traversed = [];
        $w = null;
        foreach ($this->getInputLines() as $y => $line) {
            $w ??= strlen($line);
            for ($x = 0; $x < $w; $x++) {
                $char = $line[$x];
                if ($line[$x] === 'S') {
                    $points[] = [$x, $y];
                    $char = '.';
                    $traversed[$y][$x] = [true, false];
                }
                $map[$y][] = $char;
            }
        }
        $h = count($map);
        // Le traverse!
        $step = 0;
        $steps = 1000;
        $dirs = [[0, 1], [0, -1], [1, 0], [-1, 0]];
        while ($points && $step++ < $steps) {
            $newPoints = [];
            $mod = $step % 2;
            foreach ($points as [$x, $y]) {
                foreach ($dirs as [$dirX, $dirY]) {
                    $newX = $x + $dirX;
                    $newY = $y + $dirY;
                    $mapX = $newX % $w;
                    $mapY = $newY % $h;
                    if ($mapX < 0) $mapX = $w + $mapX;
                    if ($mapY < 0) $mapY = $h + $mapY;
                    if ($map[$mapY][$mapX] === '.' && empty($traversed[$newY][$newX][$mod])) {
                        $traversed[$newY][$newX][$mod] = true;
                        $newPoints[] = [$newX, $newY];
                    }
                }
            }
            $points = $newPoints;
        }
        $sum = 0;
        $endMod = $steps % 2;
        foreach ($traversed as $points) {
            foreach ($points as $point) {
                $sum += empty($point[$endMod]) ? 0 : 1;
            }
        }
        return $sum;
    }

    protected function createImg(array $map, array $points, array $traversed, int $step): void
    {
        foreach ($traversed as $y => $traversedPoints) {
            foreach ($traversedPoints as $x => $traversedPoint) {
                $even = $traversedPoint[0] ?? false;
                $odd = $traversedPoint[1] ?? false;
                if ($odd && $even) {
                    $map[$y][$x] = 'F';
                } else {
                    $map[$y][$x] = $odd ? 'O' : 'E';
                }
            }
        }
        foreach ($points as [$x, $y]) {
            $map[$y][$x] = 'P';
        }
        $stepStr = str_pad($step, 10, '0', STR_PAD_LEFT);
        ImageOutput::map($map, "var/out/day21/$stepStr.png", 5, [
            '.' => [230, 230, 230],
            '#' => [80, 80, 80],
            'P' => [200, 200, 0],
            'O' => $step % 2 === 1 ? [255, 50, 50] : [255, 200, 200],
            'E' => $step % 2 === 0 ? [50, 50, 255] : [200, 200, 255],
            'F' => [255, 50, 255],
        ]);
    }
}
