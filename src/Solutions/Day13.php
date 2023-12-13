<?php

namespace AdventOfCode\Solutions;

use AdventOfCode\Common\Solution\AbstractSolution;

class Day13 extends AbstractSolution
{
    protected function solvePart1(): string
    {
        $maps = explode("\n\n", $this->rawInput);
        $hor = $ver = 0;
        foreach ($maps as $map) {
            $map = explode("\n", $map);
            if (($h = $this->findHorizontalMirror($map)) !== null) {
                $hor += $h;
            } else {
                $rotatedMap = $this->rotateMap($map);
                if (($v = $this->findHorizontalMirror($rotatedMap)) !== null) {
                    $ver += $v;
                }
            }
        }
        return $ver + 100 * $hor;
    }

    protected function solvePart2(): string
    {
        $maps = explode("\n\n", $this->rawInput);
        $hor = $ver = 0;
        foreach ($maps as $map) {
            $map = explode("\n", $map);
            if (($h = $this->findHorizontalMirrorWithSmudge($map)) !== null) {
                $hor += $h;
            } else {
                $rotatedMap = $this->rotateMap($map);
                if (($v = $this->findHorizontalMirrorWithSmudge($rotatedMap)) !== null) {
                    $ver += $v;
                }
            }
        }
        return $ver + 100 * $hor;
    }

    protected function findHorizontalMirror(array $map): ?int
    {
        $cnt = count($map) - 1;
        for ($r = 0; $r < $cnt; $r++) {
            if ($map[$r] === $map[$r + 1]) {
                // spread out
                $found = false;
                for ($prev = -1, $next = 2; ; $prev--, $next++) {
                    $left = $map[$r + $prev] ?? null;
                    $right = $map[$r + $next] ?? null;
                    if ($left === null || $right === null) {
                        $found = true;
                        break;
                    }
                    if ($left !== $right) {
                        break;
                    }
                }
                if ($found) {
                    return $r + 1;
                }
            }
        }
        return null;
    }

    protected function findHorizontalMirrorWithSmudge(array $map): ?int
    {
        $cnt = count($map) - 1;
        for ($r = 0; $r < $cnt; $r++) {
            $diff = $this->diff($map[$r], $map[$r+1]);
            if ($diff < 2) {
                $hasSmudge = $diff === 1;
                // spread out
                $found = false;
                for ($prev = -1, $next = 2; ; $prev--, $next++) {
                    $left = $map[$r + $prev] ?? null;
                    $right = $map[$r + $next] ?? null;
                    if ($left === null || $right === null) {
                        $found = true;
                        break;
                    }
                    $diff = $this->diff($left, $right);
                    if ($diff === 0) {
                        continue;
                    }
                    if ($diff === 1 && !$hasSmudge) {
                        $hasSmudge = true;
                        continue;
                    }
                    break;
                }
                if ($found && $hasSmudge) {
                    return $r + 1;
                }
            }
        }
        return null;
    }

    protected function diff(string $a, string $b): int
    {
        $len = strlen($a);
        $diff = 0;
        for ($i = 0; $i < $len; $i++) {
            $diff += $a[$i] === $b[$i] ? 0 : 1;
        }
        return $diff;
    }

    protected function rotateMap(array $map): array
    {
        // rotate map 90 deg so (left > right) becomes (top > bottom)
        $width = strlen($map[0]);
        $height = count($map);
        $rotatedMap = [];
        for ($x = 0; $x < $width; $x++) {
            $rotatedMap[$x] = '';
            for ($y = $height - 1; $y >= 0; $y--) {
                $rotatedMap[$x] .= $map[$y][$x];
            }
        }
        return $rotatedMap;
    }
}
