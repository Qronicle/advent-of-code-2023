<?php

namespace AdventOfCode\Solutions;

use AdventOfCode\Common\Solution\AbstractSolution;

class Day03 extends AbstractSolution
{
    protected function solvePart1(): string
    {
        $map = $this->getInputMap();
        $width = count($map[0]);
        $sum = 0;
        foreach ($map as $y => $row) {
            for ($x = 0; $x < $width; $x++) {
                if (is_numeric($row[$x])) {
                    $partNr = $row[$x];
                    $x1 = $x2 = $x;
                    while (is_numeric($row[$x2 + 1] ?? null)) {
                        $partNr .= $row[++$x2];
                        $x++;
                    }
                    $x++; // skip next dot
                    // Check rows above and below
                    $isAdjacent = false;
                    foreach ([$y - 1, $y + 1] as $checkY) {
                        if (!isset($map[$checkY])) {
                            continue;
                        }
                        for ($checkX = $x1 - 1; $checkX <= $x2 + 1; $checkX++) {
                            if ($this->isSymbol($map[$checkY][$checkX] ?? '.')) {
                                $isAdjacent = true;
                                break 2;
                            }
                        }
                    }
                    // Check left & right
                    $isAdjacent = $isAdjacent || $this->isSymbol($row[$x1 - 1] ?? '.') || $this->isSymbol($row[$x2 + 1] ?? '.');
                    if ($isAdjacent) {
                        $sum += $partNr;
                    }
                }
            }
        }
        return $sum;
    }

    protected function solvePart2(): string
    {
        $map = $this->getInputMap();
        $width = count($map[0]);
        $potentialGears = [];
        foreach ($map as $y => $row) {
            for ($x = 0; $x < $width; $x++) {
                if (is_numeric($row[$x])) {
                    $partNr = $row[$x];
                    $x1 = $x2 = $x;
                    while (is_numeric($row[$x2 + 1] ?? null)) {
                        $partNr .= $row[++$x2];
                        $x++;
                    }
                    $x++; // skip next dot
                    // Check rows above and below
                    $isAdjacent = false;
                    foreach ([$y - 1, $y + 1] as $checkY) {
                        if (!isset($map[$checkY])) {
                            continue;
                        }
                        for ($checkX = $x1 - 1; $checkX <= $x2 + 1; $checkX++) {
                            if (($map[$checkY][$checkX] ?? '.') === '*') {
                                $isAdjacent = true;
                                $potentialGears["$checkY,$checkX"][] = $partNr;
                                break 2;
                            }
                        }
                    }
                    // Check left & right
                    if (!$isAdjacent) {
                        if (($row[$x1 - 1] ?? '.') === '*') {
                            $potentialGears["$y," . ($x1 - 1)][] = $partNr;
                        } elseif (($row[$x2 + 1] ?? '.') === '*') {
                            $potentialGears["$y," . ($x2 + 1)][] = $partNr;
                        }
                    }
                }
            }
        }
        $sum = 0;
        foreach ($potentialGears as $partNrs) {
            if (count($partNrs) === 2) {
                $sum += array_product($partNrs);
            }
        }
        return $sum;
    }

    protected function isSymbol(string $value): bool
    {
        return !preg_match('/\d|\./', $value);
    }
}
