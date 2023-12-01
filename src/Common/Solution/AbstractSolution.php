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
    protected ?string $aocInputType;

    public function solve(int $part, string $inputFilename, ?string $inputType): string
    {
        $this->aocPart = $part;
        $this->aocInputType = $inputType;
        $method = 'solvePart' . $part;
        $this->rawInput = file_get_contents($inputFilename);
        return $this->$method();
    }

    abstract protected function solvePart1(): string;

    abstract protected function solvePart2(): string;

    protected function getInputLines(): array
    {
        return explode("\n", $this->rawInput);
    }
}
