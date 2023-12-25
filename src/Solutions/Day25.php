<?php

namespace AdventOfCode\Solutions;

use AdventOfCode\Common\Solution\AbstractSolution;
use Graphp\Graph\Graph;
use Graphp\GraphViz\GraphViz;

class Day25 extends AbstractSolution
{
    protected function solvePart1(): string
    {
        // Render graph

        $graph = new Graph();

        $vertices = [];
        $nodes = [];
        foreach ($this->getInputLines() as $line) {
            [$src, $targets] = explode(': ', $line);
            $newNodes = [$src, ...explode(' ', $targets)];
            foreach ($newNodes as $i => $node) {
                if (!isset($processedNodes[$node])) {
                    $vertices[$node] = $graph->createVertex(['id' => $node]);
                }
                if ($i === 0) {
                    continue;
                }
                $graph->createEdgeUndirected($vertices[$newNodes[0]], $vertices[$node]);
                $nodes[$newNodes[0]][$node] = true;
                $nodes[$node][$newNodes[0]] = true;
            }
        }

        dump('Rendering and opening graph...');
        (new GraphViz())
            ->setExecutable('neato')
            ->display($graph);

        // Input connectors (manual step - average search & input time is slept)

        dump('Reading and putting in the connections...');
        sleep(11);
        // Example connections
        // $connections = [
        //     ['bvb', 'cmg'],
        //     ['jqt', 'nvd'],
        //     ['hfx', 'pzl'],
        // ];
        $connections = [
            ['fbd', 'lzd'],
            ['fxn', 'ptq'],
            ['kcn', 'szl'],
        ];

        // Remove $connections
        foreach ($connections as [$a, $b]) {
            unset($nodes[$a][$b]);
            unset($nodes[$b][$a]);
        }

        // Pick the first node and find all connected nodes
        $searchNodes = [$connections[0][0]];
        $visitedNodes = [$searchNodes[0] => true];
        while ($searchNodes) {
            $newSearchNodes = [];
            foreach ($searchNodes as $node) {
                foreach ($nodes[$node] as $newNode => $tmp) {
                    if ($visitedNodes[$newNode] ?? false) {
                        continue;
                    }
                    $newSearchNodes[] = $newNode;
                    $visitedNodes[$newNode] = true;
                }
            }
            $searchNodes = $newSearchNodes;
        }
        $numA = count($visitedNodes);
        $numB = count($nodes) - $numA;
        return $numA * $numB;
    }

    protected function solvePart2(): string
    {
        return 'ho ho ho';
    }
}
