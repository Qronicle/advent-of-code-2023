<?php

namespace AdventOfCode\Solutions;

use AdventOfCode\Common\Solution\AbstractSolution;
use Throwable;

class Day22 extends AbstractSolution
{
    protected function solvePart1(): string
    {
        // assert($this->doBricksIntersect([[0, 0], [5, 0]], [[1, 0], [2, 0]]) === true);
        // assert($this->doBricksIntersect([[1, 0], [2, 0]], [[0, 0], [5, 0]]) === true);
        // assert($this->doBricksIntersect([[0, 0], [1, 0]], [[1, 0], [2, 0]]) === true);
        // assert($this->doBricksIntersect([[0, 0], [1, 0]], [[2, 0], [3, 0]]) === false);
        // assert($this->doBricksIntersect([[3, 0], [10, 0]], [[2, 0], [3, 0]]) === true);
        // assert($this->doBricksIntersect([[0, 0], [0, 5]], [[0, 1], [0, 2]]) === true);
        // assert($this->doBricksIntersect([[0, 0], [0, 1]], [[0, 1], [0, 2]]) === true);
        // assert($this->doBricksIntersect([[0, 0], [0, 1]], [[0, 2], [0, 3]]) === false);
        // assert($this->doBricksIntersect([[0, 3], [0, 10]], [[0, 2], [0, 3]]) === true);
        // assert($this->doBricksIntersect([[0, 0], [5, 0]], [[2, 1], [2, 3]]) === false);
        // assert($this->doBricksIntersect([[0, 0], [5, 0]], [[2, 0], [2, 3]]) === true);

        $brickQueue = [];
        foreach ($this->getInputLines() as $l => $line) {
            preg_match('/^(\d+),(\d+),(\d+)~(\d+),(\d+),(\d+)$/', $line, $matches);
            $min = [(int)$matches[1], (int)$matches[2], (int)$matches[3]];
            $max = [(int)$matches[4], (int)$matches[5], (int)$matches[6]];
            // assert($max[0] >= $min[0] && $max[1] >= $min[1] && $max[2] >= $min[2]); // asserted min is always first!
            // assert((int)($min[0] === $max[0]) + (int)($min[1] === $max[1]) + (int)($min[2] === $max[2]) >= 2); // asserted grow only in one direction
            $brickQueue[] = [$min, $max, $l];
            //$brickQueue[] = [$min, $max, chr($l + 65)];
        }
        usort($brickQueue, fn (array $a, array $b) => $a[0][2] <=> $b[0][2]);
        // // Assert correct drop order
        // $max = 0;
        // foreach ($brickQueue as $brick) {
        //     assert($brick[0][2] >= $max);
        //     $max = max($max, $brick[0][2]);
        // }
        $brickPit = [];
        $pitCount = 0;
        $supports = [];
        $supportedBy = [];
        while ($brickQueue) {
            // Assert correct pit order
            $max = 0;
            foreach ($brickPit as $brick) {
                    assert($brick[1][2] >= $max);
                    $max = max($max, $brick[1][2]);
                $min = min($min, $brick[1][2]);
            }
            $brick = array_shift($brickQueue);
            // Bricks already on the ground can just be added to the pit
            if ($brick[0][2] === 1) {
                $this->insertBrick($brickPit, $brick, $pitCount);
                continue;
            }
            // Check all bricks from last to first until we cross something
            for ($i = $pitCount - 1; $i >= 0; $i--) {
                $cmpBrick = $brickPit[$i];
                // when we're already below the top, we can ignore this one
                if ($brick[0][2] <= $cmpBrick[1][2]) {
                    continue;
                }
                // check whether there is an intersection between the two bricks
                if ($this->doBricksIntersect($brick, $cmpBrick)) {
                    // intersection found! we now drop this brick on the cmpBrick
                    $dropLen = $brick[0][2] - ($cmpBrick[1][2] + 1);
                    $brick[0][2] -= $dropLen;
                    $brick[1][2] -= $dropLen;
                    $supports[$cmpBrick[2]][] = $brick[2];
                    $supportedBy[$brick[2]][] = $cmpBrick[2];
                    // check the next bricks in the pit with the same max z value for intersections!
                    for ($ii = $i - 1; $ii >= 0; $ii--) {
                        $cmpBrick2 = $brickPit[$ii];
                        if ($cmpBrick2[1][2] !== $cmpBrick[1][2]) {
                            break;
                        }
                        if ($this->doBricksIntersect($brick, $cmpBrick2)) {
                            $supports[$cmpBrick2[2]][] = $brick[2];
                            $supportedBy[$brick[2]][] = $cmpBrick2[2];
                        }
                    }
                    // add to brick pit
                    $this->insertBrick($brickPit, $brick, $pitCount);
                    continue 2;
                }
            }
            // No intersections, we just drop it to the floor
            $dropLen = $brick[0][2] - 1;
            $brick[0][2] -= $dropLen;
            $brick[1][2] -= $dropLen;
            $this->insertBrick($brickPit, $brick, $pitCount);
        }
        $count = 0;
        foreach ($brickPit as $brick) {
            if (empty($supports[$brick[2]])) {
                $count++;
                continue;
            }
            $isRedundant = true;
            foreach ($supports[$brick[2]] as $supportedBrick) {
                if (count($supportedBy[$supportedBrick]) == 1) {
                    $isRedundant = false;
                    break;
                }
            }
            $count += (int)$isRedundant;
        }

        /*/ test output
        $map = ['x' => [], 'y' => []];
        foreach ($brickPit as $brick) {
            dump($brick);
            for ($x = $brick[0][0]; $x <= $brick[1][0]; $x++) {
                for ($y = $brick[0][1]; $y <= $brick[1][1]; $y++) {
                    for ($z = $brick[0][2]; $z <= $brick[1][2]; $z++) {
                        $map['x'][$z][$x] = $brick[2];
                        $map['y'][$z][$y] = $brick[2];
                    }
                }
            }
        }
        for ($z = 6; $z > 0; $z--) {
            for ($x = 0; $x < 3; $x++) {
                echo $map['x'][$z][$x] ?? '.';
            }
            echo "   ";
            for ($y = 0; $y < 3; $y++) {
                echo $map['y'][$z][$y] ?? '.';
            }
            echo "\n";
        }
        echo "---   ---\n\n";
        //*/

        return $count;
        // 468 = too low
        // 1477 = too high
    }

    protected function doBricksIntersect(array $brick, array $cmpBrick): bool
    {
        return !(
            // x-axis check
            $brick[0][0] > $cmpBrick[1][0] || $brick[1][0] < $cmpBrick[0][0]
            // y-axis check
            || $brick[0][1] > $cmpBrick[1][1] || $brick[1][1] < $cmpBrick[0][1]
        );
    }

    protected function insertBrick(array &$brickPit, array $brick, int &$count): void
    {
        dump('INSERT ', $brick);
        // we insert the brick based on max z value
        if (!$count) {
            dump('drop down biatch');
            $brickPit[] = $brick;
            $count++;
            return;
        }
        if ($brick[1][2] >= end($brickPit)[1][2]) {
            dump('is highest biatch');
            $brickPit[] = $brick;
            $count++;
            return;
        }
        for ($i = $count - 1; $i >= 0; $i--) {
            $cmpBrick = $brickPit[$i];
            if ($cmpBrick[1][2] <= $brick[1][2]) {
                array_splice($brickPit, $i + 1, 0, [$brick]);
                $count++;
                assert(count($brickPit) === $count);
                dump('above ' . $cmpBrick[2] . ' biatch');
                return;
            }
        }
        dd('oh noes');
    }

    protected function solvePart2(): string
    {
        return ':(';
    }
}
