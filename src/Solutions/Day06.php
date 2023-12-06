<?php

namespace AdventOfCode\Solutions;

use AdventOfCode\Common\Solution\AbstractSolution;

class Day06 extends AbstractSolution
{
    protected function solvePart1(): string
    {
        [$timeStr, $recordStr] = $this->getInputLines();
        preg_match_all('/\d+/', $timeStr, $timeMatches);
        preg_match_all('/\d+/', $recordStr, $recordMatches);
        $races = array_combine($timeMatches[0], $recordMatches[0]);

        $product = 1;
        foreach ($races as $time => $recordDist) {
            $product *= $this->getRaceWins($time, $recordDist);
        }
        return $product;
    }

    protected function solvePart2(): string
    {
        [$timeStr, $recordStr] = $this->getInputLines();
        preg_match_all('/\d+/', $timeStr, $timeMatches);
        preg_match_all('/\d+/', $recordStr, $recordMatches);
        $time = (int)implode('', $timeMatches[0]);
        $recordDist = (int)implode('', $recordMatches[0]);

        return $this->getRaceWins($time, $recordDist);
    }

    protected function getRaceWins(int $time, int $recordDist): int
    {
        $wins = 0;
        $estMaxCharge = ceil($time / 2);
        for ($chargeTime = $estMaxCharge; $chargeTime < $time; $chargeTime++) {
            $distance = ($time - $chargeTime) * $chargeTime;
            if ($distance > $recordDist) {
                $wins++;
            } else {
                break;
            }
        }
        for ($chargeTime = $estMaxCharge - 1; $chargeTime > 0; $chargeTime--) {
            $distance = ($time - $chargeTime) * $chargeTime;
            if ($distance > $recordDist) {
                $wins++;
            } else {
                break;
            }
        }
        return $wins;
    }
}
