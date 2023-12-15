<?php

namespace AdventOfCode\Solutions;

use AdventOfCode\Common\Solution\AbstractSolution;

class Day15 extends AbstractSolution
{
    protected function solvePart1(): string
    {
        $string = $this->rawInput . ',';
        $len = strlen($string);
        $hash = $total = 0;
        for ($i = 0; $i < $len; $i++) {
            $char = $string[$i];
            if ($char === ',') {
                $total += $hash;
                $hash = 0;
            } else {
                $hash = (($hash + ord($char)) * 17) % 256;
            }
        }
        return $total;
    }

    protected function solvePart2(): string
    {
        $initSequence = explode(',', $this->rawInput);
        $boxes = []; //array_fill(0, 256, []);
        foreach ($initSequence as $instruction) {
            preg_match('/^([a-z]+)([=-])(\d*)$/', $instruction, $matches);
            [, $label, $operation, $focalLength] = $matches;
            $boxIndex = $this->hash($label);
            if ($operation === '=') {
                $boxes[$boxIndex][$label] = (int)$focalLength;
            } else {
                unset($boxes[$boxIndex][$label]);
            }
        }
        $totalPower = 0;
        foreach ($boxes as $boxIndex => $lenses) {
            $lensIndex = 0;
            foreach ($lenses as $focalLength) {
                $totalPower += ($boxIndex + 1) * ++$lensIndex * $focalLength;
            }
        }
        return $totalPower;
    }

    protected function hash(string $input): int
    {
        $hash = 0;
        foreach (str_split($input) as $char) {
            $hash = (($hash + ord($char)) * 17) % 256;
        }
        return $hash;
    }
}
