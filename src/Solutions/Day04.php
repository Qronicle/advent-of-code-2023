<?php

namespace AdventOfCode\Solutions;

use AdventOfCode\Common\Solution\AbstractSolution;

class Day04 extends AbstractSolution
{
    protected function solvePart1(): string
    {
        $points = 0;
        foreach ($this->getCards() as $card) {
            $points += $card->numMatching ? (2 ** ($card->numMatching - 1)) : 0;
        }
        return $points;
    }

    protected function solvePart2(): string
    {
        $cards = $this->getCards();
        $totalCards = 0;
        foreach ($cards as $card) {
            $totalCards += $this->getNumScratchCards($card, $cards);
        }
        return $totalCards;
    }

    /**
     * @return ScratchCard[]
     */
    protected function getCards(): array
    {
        $lines = $this->getInputLines();
        [$scratched] = explode(' | ', $lines[2]);
        $numScratches = count(array_filter(explode(' ', $scratched))) - 2;

        $cards = [];
        foreach ($this->getInputLines() as $i => $line) {
            preg_match_all('/\d+/', $line, $matches);
            $scratchedNrs = array_slice($matches[0], 1, $numScratches);
            $winningNrs = array_slice($matches[0], 1 + $numScratches);
            $cards[] = new ScratchCard($i, $scratchedNrs, $winningNrs);
        }
        return $cards;
    }

    protected function getNumScratchCards(ScratchCard $card, array $cards): int
    {
        if ($card->numScratchcards !== null) {
            return $card->numScratchcards;
        }
        $sum = 1;
        for ($i = 1; $i <= $card->numMatching; $i++) {
            $sum += $this->getNumScratchCards($cards[$card->index + $i], $cards);
        }
        $card->numScratchcards = $sum;
        return $sum;
    }
}

class ScratchCard
{
    public readonly int $numMatching;
    public ?int $numScratchcards = null;

    public function __construct(
        public readonly int $index,
        public readonly array $scratchedNrs,
        public readonly array $winningNrs,
    ) {
        $this->numMatching = count(array_intersect($scratchedNrs, $winningNrs));
    }
}
