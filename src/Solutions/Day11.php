<?php

namespace AdventOfCode\Solutions;

use AdventOfCode\Common\Solution\AbstractSolution;

class Day11 extends AbstractSolution
{
    protected function solvePart1(): string
    {
        return $this->totalDist();
    }

    protected function solvePart2(): string
    {
        return $this->totalDist(1_000_000);
    }

    protected function totalDist(int $expansion = 2): int
    {
        $map = $this->getInputMap();
        $w = count($map[0]);
        $h = count($map);
        $expanders = (object)['x' => [], 'y' => []];
        $expansion--; // first step is already accounted for
        $galaxies = [];

        // Find horizontal expanders
        $numExpanders = 0;
        foreach ($map as $y => $row) {
            if (!in_array('#', $row)) {
                $numExpanders += $expansion;
            }
            $expanders->y[$y] = $numExpanders;
        }

        // Find vertical expanders & galaxies
        $numExpanders = 0;
        for ($x = 0; $x < $w; $x++) {
            $hasGalaxy = false;
            for ($y = 0; $y < $h; $y++) {
                if ($map[$y][$x] === '#') {
                    $hasGalaxy = true;
                    $galaxies[] = new Vector2($x, $y);
                }
            }
            if (!$hasGalaxy) {
                $numExpanders += $expansion;
            }
            $expanders->x[$x] = $numExpanders;
        }

        // Move galaxies
        foreach ($galaxies as $galaxy) {
            $galaxy->x += $expanders->x[$galaxy->x];
            $galaxy->y += $expanders->y[$galaxy->y];
        }

        // Calculate total distance
        $sum = 0;
        for ($currGal = 0; $currGal < count($galaxies) - 1; $currGal++) {
            for ($cmpGal = $currGal + 1; $cmpGal < count($galaxies); $cmpGal++) {
                $sum += $galaxies[$currGal]->manhattanDist($galaxies[$cmpGal]);
            }
        }
        return $sum;
    }
}

class Vector2
{
    public function __construct(
        public int $x,
        public int $y,
    ) {
    }

    public function manhattanDist(Vector2 $vector2): int
    {
        return abs($this->x - $vector2->x) + abs($this->y - $vector2->y);
    }
}
