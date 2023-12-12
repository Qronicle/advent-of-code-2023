<?php

namespace AdventOfCode\Solutions;

use AdventOfCode\Common\Solution\AbstractSolution;

class Day12 extends AbstractSolution
{
    protected array $cache = [];

    protected function solvePart1(): string
    {
        $sum = 0;
        foreach ($this->getInputLines() as $line) {
            [$springs, $damagedGroups] = explode(' ', $line);
            $damagedGroups = array_map(fn(string $val) => (int)$val, explode(',', $damagedGroups));
            $num = $this->getSum($springs, $damagedGroups);
            $sum += $num;
            $this->cache = [];
        }
        return $sum;
    }

    protected function solvePart2(): string
    {
        $sum = 0;
        foreach ($this->getInputLines() as $line) {
            [$springs, $damagedGroups] = explode(' ', $line);
            $springs = "$springs?$springs?$springs?$springs?$springs";
            $damagedGroups = "$damagedGroups,$damagedGroups,$damagedGroups,$damagedGroups,$damagedGroups";
            $damagedGroups = array_map(fn(string $val) => (int)$val, explode(',', $damagedGroups));
            $num = $this->getSum($springs, $damagedGroups);
            $sum += $num;
            $this->cache = [];
        }
        return $sum;
    }

    protected function getSum(string $springs, array $damagedGroups, int $springIndex = 0, int $groupIndex = 0, int $numDamaged = 0): int
    {
        // We will encounter the same conditions many a time when running through all permutations
        // Better cache them!
        if (isset($this->cache[$springIndex][$groupIndex][$numDamaged])) {
            return $this->cache[$springIndex][$groupIndex][$numDamaged];
        }

        // Last spring calculations!
        if ($springIndex === strlen($springs)) {
            if ($groupIndex === count($damagedGroups) && $numDamaged === 0) {
                // We already reached the required damaged groups and have no running damaged spring streak
                return 1;
            } elseif ($groupIndex === count($damagedGroups) - 1 && $damagedGroups[$groupIndex] === $numDamaged) {
                // We end with a damaged group that matches the final required damaged group length
                return 1;
            }
            // Not a valid permutation!
            return 0;
        }

        $sum = 0;
        $srcSpring = $springs[$springIndex];
        // If the damage status of the current spring is unknown, permutate the path to include both options!
        $targetSprings = $srcSpring === '?' ? ['.', '#'] : [$srcSpring];
        foreach ($targetSprings as $targetSpring) {
            if ($targetSpring === '.' && $numDamaged === 0) {
                // We are not in a damaged spring streak, continue business as usual at the next spring
                $sum += $this->getSum($springs, $damagedGroups, $springIndex + 1, $groupIndex);
            } elseif ($targetSpring === '.' && $numDamaged > 0 && ($damagedGroups[$groupIndex] ?? -1) === $numDamaged) {
                // We are in a damaged spring streak, and the number of damaged springs matches our current group
                $sum += $this->getSum($springs, $damagedGroups, $springIndex + 1, $groupIndex + 1);
            } elseif ($targetSpring === '#') {
                // We grow the damaged spring streak
                $sum += $this->getSum($springs, $damagedGroups, $springIndex + 1, $groupIndex, $numDamaged + 1);
            }
            // All other situations indicate that the required damaged groups cannot be formed anymore
        }

        // Cache and return the result son
        $this->cache[$springIndex][$groupIndex][$numDamaged] = $sum;
        return $sum;
    }
}
