<?php

namespace AdventOfCode\Solutions;

use AdventOfCode\Common\Solution\AbstractSolution;

class Day22 extends AbstractSolution
{
    protected function solvePart1(): string
    {
        [$brickPit, $supports, $supportedBy] = $this->getBrickPit();

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

        return $count;
    }

    protected function solvePart2(): string
    {
        [$brickPit, $supports, $supportedBy] = $this->getBrickPit();

        $count = 0;
        foreach ($brickPit as $brick) {
            $fallen = $this->getNumFallen($brick[2], $supports, $supportedBy);
            $count += $fallen - 1;
        }
        return $count;
    }

    protected function getNumFallen(int $brick, array $supports, array $supportedBy, array $fallen = []): int
    {
        $fallen[] = $brick;

        $bricks = $supports[$brick] ?? [];
        $waitingBricks = [];
        $waitingOnBricks = [];
        while ($bricks) {
            $newBricks = [];
            foreach ($bricks as $brick) {
                $standingSupportBricks = array_diff($supportedBy[$brick], $fallen);
                if (!$standingSupportBricks) {
                    $fallen[] = $brick;
                    foreach ($supports[$brick] ?? [] as $subBrick) {
                        $newBricks[] = $subBrick;
                    }
                    if (isset($waitingOnBricks[$brick])) {
                        foreach ($waitingOnBricks[$brick] as $waitingBrick) {
                            $standingSubSupportBricks = array_diff($waitingBricks[$waitingBrick], [$brick]);
                            if ($standingSubSupportBricks) {
                                $fallen[] = $waitingBrick;
                                foreach ($supports[$waitingBrick] ?? [] as $subBrick) {
                                    $newBricks[] = $subBrick;
                                }
                                unset($waitingBricks[$waitingBrick]);
                            } else {
                                $waitingBricks[$waitingBrick] = $standingSubSupportBricks;
                            }
                        }
                        unset($waitingOnBricks[$brick]);
                    }
                } else {
                    $waitingBricks[$brick] = $standingSupportBricks;
                    foreach ($standingSupportBricks as $standingSupportBrick) {
                        $waitingOnBricks[$standingSupportBrick][] = $brick;
                    }
                }
            }
            $bricks = array_unique($newBricks);
        }

        return count(array_unique($fallen));
    }

    protected function getBrickPit(): array
    {
        $brickQueue = [];
        foreach ($this->getInputLines() as $l => $line) {
            preg_match('/^(\d+),(\d+),(\d+)~(\d+),(\d+),(\d+)$/', $line, $matches);
            $min = [(int)$matches[1], (int)$matches[2], (int)$matches[3]];
            $max = [(int)$matches[4], (int)$matches[5], (int)$matches[6]];
            $brickQueue[] = [$min, $max, $l];
        }
        usort($brickQueue, fn (array $a, array $b) => $a[0][2] <=> $b[0][2]);
        $brickPit = [];
        $pitCount = 0;
        $supports = [];
        $supportedBy = [];
        while ($brickQueue) {
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
        return [$brickPit, $supports, $supportedBy];
    }

    protected function doBricksIntersect(array $brick, array $cmpBrick): bool
    {
        return !(
            $brick[0][0] > $cmpBrick[1][0] || $brick[1][0] < $cmpBrick[0][0] // x-axis check
            || $brick[0][1] > $cmpBrick[1][1] || $brick[1][1] < $cmpBrick[0][1] // y-axis check
        );
    }

    protected function insertBrick(array &$brickPit, array $brick, int &$count): void
    {
        // we insert the brick based on max z value
        if (!$count) {
            $brickPit[] = $brick;
            $count++;
            return;
        }
        if ($brick[1][2] >= end($brickPit)[1][2]) {
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
                return;
            }
        }
    }
}
