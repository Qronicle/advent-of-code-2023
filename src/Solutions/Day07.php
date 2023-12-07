<?php

namespace AdventOfCode\Solutions;

use AdventOfCode\Common\Solution\AbstractSolution;

class Day07 extends AbstractSolution
{
    protected function solvePart1(): string
    {
        return $this->getTotalWinnings();
    }

    protected function solvePart2(): string
    {
        CamelHand::enableJoker();
        return $this->getTotalWinnings();
    }

    protected function getTotalWinnings(): string
    {
        // Create hands
        $hands = [];
        foreach ($this->getInputLines() as $line) {
            [$cards, $bid] = explode(' ', $line);
            $hands[] = new CamelHand(str_split($cards), $bid);
        }

        // Sort hands
        usort($hands, CamelHand::compare(...));

        // Calculate total
        $total = 0;
        foreach ($hands as $i => $hand) {
            $total += ($i + 1) * $hand->bid;
        }
        return $total;
    }
}

class CamelHand
{
    protected static bool $jokerEnabled = false;
    protected static array $cardValues = ['A' => 14, 'K' => 13, 'Q' => 12, 'J' => 11, 'T' => 10];

    public readonly array $cards;
    public readonly int $value;

    public function __construct(array $cards, public readonly int $bid)
    {
        $this->cards = array_map(fn (string $value) => (int)(self::$cardValues[$value] ?? $value), $cards);
        $cardCount = [];
        foreach ($this->cards as $card) {
            $cardCount[$card] = isset($cardCount[$card]) ? $cardCount[$card] + 1 : 1;
        }
        $numCounts = [5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0];
        foreach ($cardCount as $count) {
            $numCounts[$count]++;
        }
        $this->value = self::$jokerEnabled && ($cardCount[1] ?? false)
            ? $this->calculateValueWithJoker($numCounts, $cardCount[1])
            : $this->calculateValue($numCounts);
    }

    protected function calculateValue(array $numCounts): int
    {
        if ($numCounts[5]) {
            return 7;
        }
        if ($numCounts[4]) {
            return 6;
        }
        if ($numCounts[3] && $numCounts[2]) {
            return 5;
        }
        if ($numCounts[3]) {
            return 4;
        }
        if ($numCounts[2]) {
            return $numCounts[2] == 2 ? 3 : 2;
        }
        return 1;
    }

    protected function calculateValueWithJoker(array $numCounts, int $numJokers): int
    {
        if ($numJokers === 5) {
            return 7;
        }
        $numCounts[$numJokers]--;
        // Add jokers to highest count number
        foreach ($numCounts as $num => $count) {
            if ($count === 0) continue;
            $higherNum = $num + $numJokers;
            $numCounts[$num]--;
            $numCounts[$higherNum]++;
            break;
        }
        return $this->calculateValue($numCounts);
    }

    public static function compare(CamelHand $a, CamelHand $b): int
    {
        $valueCmp = $a->value <=> $b->value;
        if ($valueCmp !== 0) {
            return $valueCmp;
        }
        for ($i = 0; $i < 5; $i++) {
            $cardCmp = $a->cards[$i] <=> $b->cards[$i];
            if ($cardCmp !== 0) {
                return $cardCmp;
            }
        }
        throw new \Exception("Naughty naughty");
    }

    public static function enableJoker(): void
    {
        self::$jokerEnabled = true;
        self::$cardValues['J'] = 1;
    }
}