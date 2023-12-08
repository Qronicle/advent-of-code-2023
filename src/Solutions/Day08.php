<?php

namespace AdventOfCode\Solutions;

use AdventOfCode\Common\Solution\AbstractSolution;

class Day08 extends AbstractSolution
{
    protected function solvePart1(): string
    {
        [$directions, $nodes] = $this->parseInput();
        $node = 'AAA';
        $mod = count($directions);
        $step = 0;
        while ($node !== 'ZZZ') {
            $node = $nodes[$node][$directions[$step++ % $mod]];
        }
        return $step;
    }

    protected function solvePart2(): string
    {
        [$directions, $nodes] = $this->parseInput();
        $startNodes = [];
        foreach ($nodes as $node => $tmp) {
            if (str_ends_with($node, 'A')) {
                $startNodes[] = $node;
            }
        }
        $lcm = null;
        foreach ($startNodes as $startNode) {
            $zNode = $this->findZNode($directions, $nodes, $startNode);
            $lcm = $lcm === null ? $zNode : least_common_multiple($lcm, $zNode);
        }
        return $lcm;
    }

    protected function findZNode(array $directions, array $nodes, string $startNode): int
    {
        $node = $startNode;
        $mod = count($directions);
        $step = 0;
        // $nodeSteps = [];
        // $zIndex = null;
        // $repStart = null;
        while (true) {
            $dirIndex = $step % $mod;
            if (str_ends_with($node, 'Z')) {
                return $step;
            }
            // Turns out we don't need any of this jazz
            //if (isset($nodeSteps[$dirIndex][$node])) {
            //    if (!$repStart) {
            //        dump("loop after $step steps! (from node $node at step {$nodeSteps[$dirIndex][$node]})");
            //    }
            //    $repStart = $nodeSteps[$dirIndex][$node];
            //    //break;
            //}
            //$nodeSteps[$dirIndex][$node] = $step;
            $node = $nodes[$node][$directions[$dirIndex]];
            ++$step;
        }
    }

    protected function parseInput(): array
    {
        [$directions, $points] = explode("\n\n", $this->rawInput);
        $directions = str_split($directions);
        $pointLines = explode("\n", $points);
        $nodes = [];
        foreach ($pointLines as $pointLine) {
            preg_match_all('/[0-9A-Z]{3}/', $pointLine, $pointMatches);
            $nodes[$pointMatches[0][0]] = ['L' => $pointMatches[0][1], 'R' => $pointMatches[0][2]];
        }
        return [$directions, $nodes];
    }
}
