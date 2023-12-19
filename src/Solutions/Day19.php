<?php

namespace AdventOfCode\Solutions;

use AdventOfCode\Common\Solution\AbstractSolution;

class Day19 extends AbstractSolution
{
    protected function solvePart1(): string
    {
        [$formulas, $parts] = explode("\n\n", $this->rawInput);
        $formulas = $this->parseFormulas(explode("\n", $formulas));
        $parts = $this->parseParts(explode("\n", $parts));
        $sum = 0;
        foreach ($parts as $part) {
            //dump('', $part, '');
            $result = 'in';
            extract($part);
            do {
                $result = eval($formulas[$result]);
                //dump(" > $result");
            } while ($result !== 'A' && $result !== 'R');
            if ($result === 'A') {
                $sum += array_sum($part);
            }
        }
        return $sum;
    }

    protected function solvePart2(): string
    {
        return ':(';
    }

    protected function parseFormulas(array $formulaStrings): array
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

    protected function parseParts(array $parts): array
    {
        $parsedParts = [];
        foreach ($parts as $part) {
            preg_match_all('/([a-z]+)=(\d+)[,}]/', $part, $matches);
            $parsedParts[] = array_combine($matches[1], $matches[2]);
        }
        return $parsedParts;
    }
}
