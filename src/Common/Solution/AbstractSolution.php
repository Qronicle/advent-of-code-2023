<?php

namespace AdventOfCode\Common\Solution;

/**
 * Class AbstractSolution
 *
 * @package AdventOfCode\Common\Solution
 * @author  Ruud Seberechts
 */
abstract class AbstractSolution
{
    protected string $rawInput;
    protected int $aocPart;

    public function solve(int $part, string $input): string
    {
        $this->aocPart = $part;
        $method = 'solvePart' . $part;
        $this->rawInput = $input;
        return $this->$method();
    }

    abstract protected function solvePart1(): int|string;

    abstract protected function solvePart2(): int|string;

    protected function getInputLines(): array
    {
        return explode("\n", $this->rawInput);
    }

    protected function getInputMap(?string $input = null): array
    {
        $input ??= $this->rawInput;
        return array_map(fn (string $val) => str_split($val), explode("\n", $input));
    }
}
