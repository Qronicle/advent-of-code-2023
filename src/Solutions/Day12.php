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
            $springs = str_split($springs);
            $damagedGroups = explode(',', $damagedGroups);
            $sum += $this->getNumPossibilities($springs, $damagedGroups);
        }
        return $sum;
    }

    protected function solvePart2(): string
    {
        return $this->attempt2();
        ini_set('memory_limit', '12G');
        $sum = 0;
        foreach ($this->getInputLines() as $l => $line) {
            if ($l <= 15) continue;
            [$springs, $damagedGroups] = explode(' ', $line);
            if (false) {
                $springs = "$springs?$springs?$springs?$springs?$springs";
                $damagedGroups = "$damagedGroups,$damagedGroups,$damagedGroups,$damagedGroups,$damagedGroups";
                $num = $this->getNumPossibilities(str_split($springs), explode(',', $damagedGroups));
            } else {
                $num1 = $this->getNumPossibilities(str_split($springs), explode(',', $damagedGroups));
                $numX = $this->getNumPossibilities(str_split("$springs?$springs"), explode(',', "$damagedGroups,$damagedGroups"));
                $num2 = $numX / $num1;
                if (str_contains((string)$num2, '.')) {
                    $springs = "$springs?$springs?$springs?$springs?$springs";
                    $damagedGroups = "$damagedGroups,$damagedGroups,$damagedGroups,$damagedGroups,$damagedGroups";
                    $num = $this->getNumPossibilities(str_split($springs), explode(',', $damagedGroups));
                } else {
                    $num = $num1 * $num2 * $num2 * $num2 * $num2;
                }
            }
            $sum += $num;
            echo "$l. $num ($sum)\n";
        }
        return $sum;
    }

    protected function attempt2(): int
    {
        $sum = 0;
        foreach ($this->getInputLines() as $l => $line) {
            [$springs, $damagedGroups] = explode(' ', $line);
            $springs = "$springs?$springs?$springs?$springs?$springs";
            $damagedGroups = "$damagedGroups,$damagedGroups,$damagedGroups,$damagedGroups,$damagedGroups";
            $damagedGroups = array_map(fn (string $val) => (int)$val, explode(',', $damagedGroups));
            $num = $this->getSum($springs, $damagedGroups);
            echo "$l. $num ($sum)\n";
            $sum += $num;
            $this->cache = [];
        }
        return $sum;
    }

    protected function getSum(string $springs, array $damagedGroups, int $springIndex = 0, int $groupIndex = 0, int $numDamaged = 0): int
    {
        if (isset($this->cache[$springIndex][$groupIndex][$numDamaged])) {
            return $this->cache[$springIndex][$groupIndex][$numDamaged];
        }

        // Last spring calculations!
        if ($springIndex === strlen($springs)) {
            if ($groupIndex === count($damagedGroups) && $numDamaged === 0) {
                return 1;
            } elseif ($groupIndex === count($damagedGroups) - 1 && $damagedGroups[$groupIndex] === $numDamaged) {
                return 1;
            } else {
                return 0;
            }
        }

        $sum = 0;
        foreach (['.', '#'] as $spring) {
            if ($springs[$springIndex] === $spring || $springs[$springIndex] === '?') {
                if ($spring === '.' && $numDamaged === 0) {
                    $sum += $this->getSum($springs, $damagedGroups, $springIndex + 1, $groupIndex);
                } elseif ($spring === '.' && $numDamaged > 0 && $groupIndex < count($damagedGroups) && $damagedGroups[$groupIndex] === $numDamaged) {
                    $sum += $this->getSum($springs, $damagedGroups, $springIndex + 1, $groupIndex + 1);
                } elseif ($spring === '#') {
                    $sum += $this->getSum($springs, $damagedGroups, $springIndex + 1, $groupIndex, $numDamaged + 1);
                }
            }
        }
        $this->cache[$springIndex][$groupIndex][$numDamaged] = $sum;
        return $sum;
    }

    protected function getNumPossibilities(array $springs, array $damagedGroups): int
    {
        $sum = 0;
        $possibilities = [''];
        foreach ($springs as $spring) {
            $newPossibilities = [];
            if ($spring === '?') {
                foreach ($possibilities as $possibility) {
                    $newPossibilities[] = $possibility . '.';
                    $newPossibilities[] = $possibility . '#';
                }
            } else {
                foreach ($possibilities as $possibility) {
                    $newPossibilities[] = $possibility . $spring;
                }
            }
            $possibilities = [];
            foreach ($newPossibilities as $possibility) {
                if ($this->canHaveValidDamagedGroups($possibility, $damagedGroups)) {
                    $possibilities[] = $possibility;
                }
            }
        }
        foreach ($possibilities as $possibility) {
            if ($this->hasValidDamagedGroups($possibility, $damagedGroups)) {
                $sum++;
            }
        }
        return $sum;
    }

    protected function hasValidDamagedGroups(string $springs, array $damagedGroups): bool
    {
        $inGroup = false;
        $numInGroup = 0;
        $springs .= '.';
        foreach (str_split($springs) as $spring) {
            if ($spring === '#') {
                $inGroup = true;
                $numInGroup++;
            } elseif ($inGroup) {
                if (!$damagedGroups || array_shift($damagedGroups) != $numInGroup) {
                    return false;
                }
                $inGroup = false;
                $numInGroup = 0;
            }
        }
        return !$damagedGroups;
    }

    protected function canHaveValidDamagedGroups(string $springs, array $damagedGroups): bool
    {
        $inGroup = false;
        $numInGroup = 0;
        foreach (str_split($springs) as $spring) {
            if ($spring === '#') {
                $inGroup = true;
                $numInGroup++;
            } elseif ($inGroup) {
                if (!$damagedGroups || array_shift($damagedGroups) != $numInGroup) {
                    return false;
                }
                $inGroup = false;
                $numInGroup = 0;
            }
        }
        return true;
    }
}
