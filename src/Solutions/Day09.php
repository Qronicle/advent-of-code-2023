<?php

namespace AdventOfCode\Solutions;

use AdventOfCode\Common\Solution\AbstractSolution;

class Day09 extends AbstractSolution
{
    protected function solvePart1(): string
    {
        $sum = 0;
        foreach ($this->getInputLines() as $line) {
            $numbers = $this->extrapolate(explode(' ', $line));
            $last = 0;
            for ($depth = count($numbers) - 2; $depth >= 0; $depth--) {
                $last = end($numbers[$depth]) + $last;
            }
            $sum += $last;
        }
        return $sum;
    }

    protected function solvePart2(): string
    {
        $sum = 0;
        foreach ($this->getInputLines() as $line) {
            $numbers = $this->extrapolate(explode(' ', $line));
            $first = 0;
            for ($depth = count($numbers) - 2; $depth >= 0; $depth--) {
                $first = reset($numbers[$depth]) - $first;
            }
            $sum += $first;
        }
        return $sum;
    }

    protected function extrapolate(array $numbers): array
    {
        $numbers = [$numbers];
        $depth = 0;
        do {
            $count = count($numbers[$depth]);
            $prev = $numbers[$depth][0];
            $allZeroes = true;
            for ($i = 1; $i < $count; $i++) {
                $curr = $numbers[$depth][$i];
                $diff = $curr - $prev;
                $numbers[$depth + 1][] = $diff;
                if ($allZeroes && $diff !== 0) {
                    $allZeroes = false;
                }
                $prev = $curr;
            }
            $depth++;
        } while (!$allZeroes);
        return $numbers;
    }
}
