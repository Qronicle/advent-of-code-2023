<?php

namespace AdventOfCode\Solutions;

use AdventOfCode\Common\Solution\AbstractSolution;

class Day23 extends AbstractSolution
{
    protected function solvePart1(): string
    {
        $nodes = $this->getNodes();
        return $this->findLongest(reset($nodes));
    }

    protected function solvePart2(): string
    {
        $nodes = $this->getCrossNodes();
        return $this->findLongest(reset($nodes), [reset($nodes)]);
    }

    protected function findLongest(Node $node, array $visitedNodes = [], int $length = 0): int
    {
        if (!$node->out) {
            return $length;
        }

        $max = 0;
        foreach ($node->out as $conn) {
            if (!isset($visitedNodes[$conn->end->key])) {
                $pathVisited = $visitedNodes;
                $pathVisited[$conn->end->key] = $conn->end;
                $max = max($max, $this->findLongest($conn->end, $pathVisited, $length + $conn->length));
            }
        }
        return $max;
    }

    /** @return array<string,Node> */
    protected function getNodes(): array
    {
        $map = $this->getInputMap();
        $h = count($map);
        $w = count($map[0]);
        $end = [$w - 2, $h - 1];
        $map[0][1] = 'v';
        $map[$end[1]][$end[0]] = 'v';
        $dirs = [
            'v' => [0, 1],
            '^' => [0, -1],
            '>' => [1, 0],
            '<' => [-1, 0],
        ];
        /** @var array<string,Node> $nodes */
        $nodes = [
            '1,0'              => new Node(1, 0),
            implode(',', $end) => new Node($end[0], $end[1]),
        ];
        $slopes = [[1, 0, 'v']];
        while ($slopes) {
            $slope = array_pop($slopes);
            $startNodeKey = $slope[0] . ',' . $slope[1];
            $startNode = $nodes[$startNodeKey];
            $visited = [$slope[1] => [$slope[0] => true]];
            // take the first step
            $points = [[$slope[0] + $dirs[$slope[2]][0], $slope[1] + $dirs[$slope[2]][1]]];
            $visited[$points[0][1]][$points[0][0]] = true;
            $steps = 1;
            while ($points) {
                $steps++;
                $newPoints = [];
                foreach ($points as [$startX, $startY]) {
                    foreach ($dirs as $dir) {
                        $x = $startX + $dir[0];
                        $y = $startY + $dir[1];
                        $tile = $map[$y][$x];
                        if ($tile === '#') {
                            continue;
                        }
                        if (isset($visited[$y][$x])) {
                            continue;
                        }
                        $visited[$y][$x] = true;

                        if ($tile === '.') {
                            // continue walking on new tiles
                            $newPoints[] = [$x, $y];
                            continue;
                        }
                        // handle (new?) node when the direction matches
                        if ($dir === $dirs[$tile]) {
                            $nodeKey = "$x,$y";
                            $node = $nodes[$nodeKey] ?? null;
                            if (!$node) {
                                $node = new Node($x, $y);
                                $nodes[$nodeKey] = $node;
                                $slopes[] = [$x, $y, $tile];
                            }
                            // add connection
                            $connection = new NodeConnection($startNode, $node, $steps);
                            $startNode->out[] = $connection;
                        }
                    }
                }
                $points = $newPoints;
            }
        }
        return $nodes;
    }

    /** @return array<string,Node> */
    protected function getCrossNodes(): array
    {
        $map = $this->getInputMap();
        $h = count($map);
        $w = count($map[0]);
        $end = [$w - 2, $h - 1];
        $map[0][1] = 'v';
        $map[$end[1]][$end[0]] = 'v';
        $dirs = [
            'v' => [0, 1],
            '^' => [0, -1],
            '>' => [1, 0],
            '<' => [-1, 0],
        ];
        /** @var array<string,Node> $nodes */
        $nodes = [
            '1,0'              => new Node(1, 0),
            implode(',', $end) => new Node($end[0], $end[1]),
        ];
        $crossRoads = [[1, 0]];
        while ($crossRoads) {
            $crossRoad = array_pop($crossRoads);
            $startNodeKey = $crossRoad[0] . ',' . $crossRoad[1];
            $startNode = $nodes[$startNodeKey];
            $visited = [$crossRoad[1] => [$crossRoad[0] => true]];
            $steps = 0;
            $points = [[$crossRoad[0], $crossRoad[1]]];
            while ($points) {
                $steps++;
                $newPoints = [];
                foreach ($points as [$startX, $startY]) {
                    foreach ($dirs as $dir) {
                        $x = $startX + $dir[0];
                        $y = $startY + $dir[1];
                        $tile = $map[$y][$x] ?? '#';
                        if ($tile === '#') {
                            continue;
                        }
                        if (isset($visited[$y][$x])) {
                            continue;
                        }
                        $visited[$y][$x] = true;

                        // check whether we have arrived at a crossroad

                        // how many dirs can we go in?
                        if ($y === 0 || $y === $h - 1) {
                            // start and end tiles should be registered as nodes
                            $numDirs = 4;
                        } else {
                            $numDirs = 0;
                            foreach ($dirs as $subDir) {
                                if ($map[$y + $subDir[1]][$x + $subDir[0]] !== '#') {
                                    $numDirs++;
                                }
                            }
                        }
                        // Handle crossroad
                        if ($numDirs > 2) {
                            $nodeKey = "$x,$y";
                            $node = $nodes[$nodeKey] ?? null;
                            if ($node === $startNode) {
                                dd('shud not happen');
                            }
                            if (!$node) {
                                $node = new Node($x, $y);
                                $nodes[$nodeKey] = $node;
                                $crossRoads[] = [$x, $y];
                            }
                            // add connection
                            $connection = new NodeConnection($startNode, $node, $steps);
                            $startNode->out[] = $connection;
                            continue; // we no longer continue on this path
                        }

                        $newPoints[] = [$x, $y];
                    }
                }
                $points = $newPoints;
            }
        }
        return $nodes;
    }
}

class Node
{
    public readonly string $key;

    public function __construct(
        public readonly int $x,
        public readonly int $y,
        /** @var NodeConnection[] */
        public array $out = [],
    ) {
        $this->key = (string)$this;
    }

    public function __toString(): string
    {
        return "$this->x,$this->y";
    }
}

class NodeConnection
{
    public function __construct(
        public readonly Node $start,
        public readonly Node $end,
        public readonly int $length,
    ) {
    }
}