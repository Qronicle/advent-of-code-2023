<?php

namespace AdventOfCode\Solutions;

use AdventOfCode\Common\Solution\AbstractSolution;

class Day19 extends AbstractSolution
{
    // Part 1 //////////////////////////////////////////////////////////////////////////////////////////////////////////

    protected function solvePart1(): string
    {
        [$formulas, $parts] = explode("\n\n", $this->rawInput);
        $formulas = $this->parseEvalFormulas(explode("\n", $formulas));
        $parts = $this->parseParts(explode("\n", $parts));
        $sum = 0;
        foreach ($parts as $part) {
            $result = 'in';
            extract($part);
            do {
                $result = eval($formulas[$result]);
            } while ($result !== 'A' && $result !== 'R');
            if ($result === 'A') {
                $sum += array_sum($part);
            }
        }
        return $sum;
    }

    protected function parseParts(array $parts): array
    {
        $parsedParts = [];
        foreach ($parts as $part) {
            preg_match_all('/([a-z]+)=(\d+)[,}]/', $part, $matches);
            $parsedParts[] = array_combine($matches[1], $matches[2]);
        }
        return $parsedParts;
    }

    protected function parseEvalFormulas(array $formulaStrings): array
    {
        $formulas = [];
        foreach ($formulaStrings as $formulaString) {
            preg_match('/^([a-z]+){(.*)}$/', $formulaString, $matches);
            [, $formulaName, $ruleStrings] = $matches;
            $ruleStrings = explode(',', $ruleStrings);
            $rule = '';
            while ($ruleStrings) {
                $ruleString = array_pop($ruleStrings);
                if (!$rule) {
                    $rule = json_encode($ruleString);
                    continue;
                }
                preg_match('/^([a-z]+)([<=>])(\d+):([a-z]+)$/i', $ruleString, $matches);
                [, $var, $operator, $value, $ref] = $matches;
                $ref = json_encode($ref);
                $rule = "\$$var $operator $value ? $ref : ($rule)";
            }
            $formulas[$formulaName] = 'return ' . $rule . ';';
        }
        return $formulas;
    }

    // Part 2 //////////////////////////////////////////////////////////////////////////////////////////////////////////

    protected function solvePart2(): string
    {
        [$formulas] = explode("\n\n", $this->rawInput);
        $formulas = $this->parseFormulas(explode("\n", $formulas));

        $ranges = [
            'x' => [1, 4000],
            'm' => [1, 4000],
            'a' => [1, 4000],
            's' => [1, 4000],
        ];

        return $this->countAccepted('in', $formulas, $ranges);
    }

    protected function countAccepted(string $formulaKey, array $formulas, array $ranges): int
    {
        // When one of the ranges is empty, or the formula key indicates rejection, there is nothing to be found here
        if (in_array(null, $ranges) || $formulaKey === 'R') {
            return 0;
        }
        // When the formula key indicates acceptance, multiple all them ranges
        if ($formulaKey === 'A') {
            return array_reduce(
                $ranges,
                fn(int $product, array $range) => $product * ($range[1] - $range[0] + 1),
                1,
            );
        }
        // Parse the formula until we find acceptance
        $formula = $formulas[$formulaKey];
        $sum = 0;
        foreach ($formula as $rule) {
            if (isset($rule['var'])) {
                [$inRanges, $ranges] = $this->splitRanges($ranges, $rule['var'], $rule['operator'], $rule['value']);
                $sum += $this->countAccepted($rule['target'], $formulas, $inRanges);
            } else {
                $sum += $this->countAccepted($rule['target'], $formulas, $ranges);
            }
        }
        return $sum;
    }

    protected function splitRanges(array $ranges, string $var, string $operator, int $value): array
    {
        $varRange = $ranges[$var];
        $inRanges = $ranges;
        $outRanges = $ranges;
        if ($operator === '>' && $value + 1 >= $varRange[0] && $value < $varRange[1]) {
            $inRange = [$value + 1, $varRange[1]];
            $outRange = $varRange[0] > $value ? null : [$varRange[0], $value];
        } elseif ($operator === '<' && $value - 1 <= $varRange[1] && $value > $varRange[0]) {
            $inRange = [$varRange[0], $value - 1];
            $outRange = $value > $varRange[1] ? null : [$value, $varRange[1]];
        } else {
            // no intersection => all in out
            $inRange = null;
            $outRange = $varRange;
        }
        $inRanges[$var] = $inRange;
        $outRanges[$var] = $outRange;
        return [$inRanges, $outRanges];
    }

    protected function parseFormulas(array $formulaStrings): array
    {
        $formulas = [];
        foreach ($formulaStrings as $formulaString) {
            preg_match('/^([a-z]+){(.*)}$/', $formulaString, $matches);
            [, $formulaName, $ruleStrings] = $matches;
            $ruleStrings = explode(',', $ruleStrings);
            $rules = [];
            while ($ruleStrings) {
                $ruleString = array_pop($ruleStrings);
                if (!$rules) {
                    $rules[] = ['target' => $ruleString];
                    continue;
                }
                preg_match('/^([a-z]+)([<=>])(\d+):([a-z]+)$/i', $ruleString, $matches);
                [, $var, $operator, $value, $ref] = $matches;
                array_unshift($rules, [
                    'var'      => $var,
                    'operator' => $operator,
                    'value'    => $value,
                    'target'   => $ref,
                ]);
            }
            $formulas[$formulaName] = $rules;
        }
        return $formulas;
    }
}
