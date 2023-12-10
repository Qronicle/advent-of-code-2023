<?php

namespace AdventOfCode\Solutions;

use AdventOfCode\Common\Solution\AbstractSolution;

class Day10 extends AbstractSolution
{
    protected function solvePart1(): string
    {
        $path = $this->getPath()[0];
        return count($path) / 2;
    }

    protected function solvePart2(): string
    {
        [$path, $dirs, $map] = $this->getPath();
        $w = count($map[0]);
        $h = count($map);
        $pathMemo = [];
        foreach ($path as $i => $position) {
            $pathMemo[(string)$position] = $i;
        }
        // Find the leftmost pipe that runs vertically, so we can run from there (yes, we assume there is one)
        $startPos = null;
        for ($y = 0; $y < $h; $y++) {
            for ($x = 0; $x < $w; $x++) {
                if (isset($pathMemo["$x,$y"])) {
                    if ($map[$y][$x] === '|') {
                        $startPos = $pathMemo["$x,$y"];
                        break 2;
                    }
                    break;
                }
            }
        }
        if ($startPos === null) {
            throw new \RuntimeException("o jeez");
        }
        // Which side is inside? (18:48)
        $inside = $path[$startPos + 1]->y > $path[$startPos]->y ? 'L' : 'R';
        // Find them apples
        $insideMemo = [];
        $pathLength = count($path);
        for ($p = 0; $p < $pathLength; $p++) {
            $i = ($p + $startPos) % $pathLength;
            $pos = clone $path[$i];
            foreach ($this->getInsideDirs($map[$pos->y][$pos->x], $dirs[$i], $inside) as $dir) {
                $pos = clone $path[$i];
                while (true) {
                    $pos->add($dir);
                    if (isset($pathMemo[(string)$pos])) {
                        break;
                    }
                    $insideMemo[(string)$pos] = true;
                }
            }
        }
        return count($insideMemo);
    }

    protected function getInsideDirs(string $pipe, Vector2 $dir, string $inside): array
    {
        return match ($pipe) {
            '|' => $inside === 'R'
                ? ($dir->y === -1 ? [new Vector2(1, 0)] : [new Vector2(-1, 0)])
                : ($dir->y === 1 ? [new Vector2(1, 0)] : [new Vector2(-1, 0)]),
            '-' => $inside === 'R'
                ? ($dir->x === 1 ? [new Vector2(0, 1)] : [new Vector2(0, -1)])
                : ($dir->x === -1 ? [new Vector2(0, 1)] : [new Vector2(0, -1)]),
            'L' => ($inside === 'L' && ($dir->y === 1 || $dir->x === 1))
                || ($inside === 'R' && ($dir->y === -1 || $dir->x === -1)) ? [] : [new Vector2(-1, 0), new Vector2(0, 1)],
            'J' => ($inside === 'L' && ($dir->y === -1 || $dir->x === 1))
                || ($inside === 'R' && ($dir->y === 1 || $dir->x === -1)) ? [] : [new Vector2(1, 0), new Vector2(0, 1)],
            '7' => ($inside === 'L' && ($dir->y === -1 || $dir->x === -1))
                || ($inside === 'R' && ($dir->y === 1 || $dir->x === 1)) ? [] : [new Vector2(1, 0), new Vector2(0, -1)],
            'F' => ($inside === 'L' && ($dir->y === 1 || $dir->x === -1))
                || ($inside === 'R' && ($dir->y === -1 || $dir->x === 1)) ? [] : [new Vector2(-1, 0), new Vector2(0, -1)],
            'S' => [], // shouldn't matter anymore
        };
    }

    /**
     * @return array{array<Vector2>,array<Vector2>,array<string[]>}
     */
    protected function getPath(): array
    {
        $pipes = $this->getPipes();
        $map = $this->getInputMap();
        $pos = $this->getStartCoords($map);
        $pipe = $pipes['S'];
        $positions = [];
        $dirs = [];
        $dir = null;
        // find first valid dir
        foreach ($pipe->exits as $dir) {
            $symbol = $map[$pos->y + $dir->y][$pos->x + $dir->x] ?? '.';
            if (isset($pipes[$symbol])) {
                if ($exit = $pipes[$symbol]->getExit($dir)) {
                    $pos->add($dir);
                    $positions[] = clone $pos;
                    $dirs[] = $dir;
                    $dir = $exit;
                    break;
                }
            }
        }
        while (true) {
            $pos->add($dir);
            $symbol = $map[$pos->y][$pos->x] ?? '.';
            $positions[] = clone $pos;
            $dirs[] = $dir;
            if ($symbol === 'S') {
                break;
            }
            $dir = $pipes[$symbol]->getExit($dir);
        }
        return [$positions, $dirs, $map];
    }

    /**
     * @return array<string,Pipe>
     */
    protected function getPipes(): array
    {
        return [
            '|' => new Pipe([new Vector2(0, -1), new Vector2(0, 1)]),
            '-' => new Pipe([new Vector2(-1, 0), new Vector2(1, 0)]),
            'L' => new Pipe([new Vector2(0, -1), new Vector2(1, 0)]),
            'J' => new Pipe([new Vector2(0, -1), new Vector2(-1, 0)]),
            '7' => new Pipe([new Vector2(0, 1), new Vector2(-1, 0)]),
            'F' => new Pipe([new Vector2(0, 1), new Vector2(1, 0)]),
            'S' => new Pipe([new Vector2(0, 1), new Vector2(0, -1), new Vector2(1, 0), new Vector2(-1, 0)]),
        ];
    }

    protected function getStartCoords(array $map): Vector2
    {
        foreach ($map as $y => $row) {
            foreach ($row as $x => $point) {
                if ($point === 'S') {
                    return new Vector2($x, $y);
                }
            }
        }
    }
}

class Pipe
{
    public function __construct(
        /** @var Vector2[] */
        public array $exits,
    ) {
    }

    public function getExit($incomingDirection): ?Vector2
    {
        foreach ($this->exits as $i => $exit) {
            if ($exit->x === -$incomingDirection->x && $exit->y === -$incomingDirection->y) {
                return $this->exits[$i ? 0 : 1]; // assume only two exists
            }
        }
        return null;
    }
}

class Vector2
{
    public function __construct(
        public int $x,
        public int $y,
    ) {
    }

    public function add(Vector2 $vector2): static
    {
        $this->x += $vector2->x;
        $this->y += $vector2->y;
        return $this;
    }

    public function __toString(): string
    {
        return $this->x . ',' . $this->y;
    }
}
