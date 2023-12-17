<?php

namespace AdventOfCode\Solutions;

use AdventOfCode\Common\Solution\AbstractSolution;

class Day17 extends AbstractSolution
{
    protected function solvePart1(int $minLen = 1, int $maxLen = 3): string
    {
        ini_set('memory_limit', '2G');

        // dirs
        $outgoingDirs = [
            Direction::UP    => [Direction::RIGHT => [1, 0], Direction::LEFT => [-1, 0]],
            Direction::RIGHT => [Direction::DOWN => [0, 1], Direction::UP => [0, -1]],
            Direction::DOWN  => [Direction::RIGHT => [1, 0], Direction::LEFT => [-1, 0]],
            Direction::LEFT  => [Direction::DOWN => [0, 1], Direction::UP => [0, -1]],
        ];

        // Create map
        /** @var array<int,array<int,Tile> $map */
        $map = [];
        foreach ($this->getInputLines() as $y => $line) {
            $map[] = array_map(fn(string $value) => new Tile($value), str_split($line));
        }
        $width = count($map[0]);
        $height = count($map);
        $targetX = $width - 1;
        $targetY = $height - 1;
        // Indicate that going out of bounds makes no sense
        for ($x = 0; $x < $width; $x++) {
            $map[0][$x]->minDirectionValue[Direction::UP] = -1;
            $map[$targetY][$x]->minDirectionValue[Direction::DOWN] = -1;
        }
        for ($y = 0; $y < $height; $y++) {
            $map[$y][0]->minDirectionValue[Direction::LEFT] = -1;
            $map[$y][$targetX]->minDirectionValue[Direction::RIGHT] = -1;
        }
        /** @var Route[] $routes */
        $routes = [new Route(0, 0, Direction::RIGHT), new Route(0, 0, Direction::DOWN)];
        $min = null;
        while ($routes) {
            $newRoutes = [];
            foreach ($routes as $route) {
                if ($route->x === $targetX && $route->y === $targetY) {
                    if (!$min || $route->value < $min) {
                        $min = $route->value;
                    }
                    continue;
                }
                $tile = $map[$route->y][$route->x];
                foreach ($outgoingDirs[$route->direction] as $direction => $coords) {
                    // check whether the route value is lower than the lowest one that started here in this direction
                    if ($tile->minDirectionValue[$direction] <= $route->value) {
                        continue;
                    }
                    $tile->minDirectionValue[$direction] = $route->value;
                    // Add three new routes
                    $newValue = $route->value;
                    //$newCoords = $route->coords;
                    for ($len = 1; $len <= $maxLen; $len++) {
                        $x = $route->x + $coords[0] * $len;
                        $y = $route->y + $coords[1] * $len;
                        if (!isset($map[$y][$x])) {
                            break;
                        }
                        $newValue += $map[$y][$x]->value;
                        if ($len < $minLen) {
                            continue;
                        }
                        $estValue = ($targetY - $y) + ($targetX - $x) + $newValue; // best (im)possible outcome
                        $newRoutes[] = new Route($x, $y, $direction, $newValue, $estValue);
                    }
                }
            }
            $routes = $newRoutes;
        }
        return $min;
    }

    protected function solvePart2(): string
    {
        return $this->solvePart1(4, 10);
    }
}

class Tile
{
    public array $minDirectionValue;

    public function __construct(
        public int $value,
    ) {
        $this->minDirectionValue = array_fill(0, 4, PHP_INT_MAX);
    }
}

class Route
{
    public function __construct(
        public int $x,
        public int $y,
        public int $direction,
        public int $value = 0,
        public int $estValue = 0,
    ) {
    }
}

class Direction
{
    public const UP    = 0;
    public const RIGHT = 1;
    public const DOWN  = 2;
    public const LEFT  = 3;
}