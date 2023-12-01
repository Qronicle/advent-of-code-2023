<?php

namespace AdventOfCode\Solutions;

use AdventOfCode\Common\Solution\AbstractSolution;

class Day01 extends AbstractSolution
{
    protected function solvePart1(): string
    {
        $sum = 0;
        foreach ($this->getInputLines() as $line) {
            preg_match_all('/\d/', $line, $digits);
            $number = (int)(reset($digits[0]) . end($digits[0]));
            $sum += $number;
        }
        return $sum;
    }

    protected function solvePart2(): string
    {
        $sum = 0;
        foreach ($this->getInputLines() as $line) {
            preg_match_all('/(?=(\d|one|two|three|four|five|six|seven|eight|nine))/', $line, $digits);
            $number = (int)($this->getNumber(reset($digits[1])) . $this->getNumber(end($digits[1])));
            $sum += $number;
        }
        return $sum;
    }

    protected function getNumber(string $number): int
    {
        if (is_numeric($number)) {
            return (int)$number;
        }
        return match ($number) {
            'one'   => 1,
            'two'   => 2,
            'three' => 3,
            'four'  => 4,
            'five'  => 5,
            'six'   => 6,
            'seven' => 7,
            'eight' => 8,
            'nine'  => 9,
        };
    }
}
