<?php

namespace AdventOfCode\Solutions;

use AdventOfCode\Common\Solution\AbstractSolution;

class Day05 extends AbstractSolution
{
    protected function solvePart1(): string
    {
        $almanac = new Almanac($this->rawInput);
        $min = PHP_INT_MAX;
        foreach ($almanac->getSeeds() as $seed) {
            $min = min($min, $almanac->getLocation($seed));
        }
        return $min;
    }

    protected function solvePart2(): string
    {
        $almanac = new Almanac($this->rawInput);
        $min = PHP_INT_MAX;
        $seeds = $almanac->getSeeds();
        for ($i = 0; $i < count($seeds); $i += 2) {
            $location = $almanac->getLowestLocationInRange($seeds[$i], $seeds[$i + 1]);
            $min = min($min, $location);
        }
        return $min;
    }
}

class Almanac
{
    /** @var int[] */
    protected array $seeds;

    /** @var Range[] */
    protected array $map;

    public function __construct(string $content)
    {
        $parts = explode("\n\n", $content);

        // Seeds
        preg_match_all('/\d+/', array_shift($parts), $seedMatches);
        $this->seeds = $seedMatches[0];

        // Mapping
        $mappings = [];
        foreach ($parts as $part) {
            $map = explode("\n", $part);
            array_shift($map);
            $map = array_map(fn(string $value) => explode(' ', $value), $map);
            $mappings[] = $this->createReverseMap($map);
        }

        $this->map = $this->flattenMaps($mappings);
    }

    public function flattenMaps(array $maps): array
    {
        $ranges = [];
        $maxSource = -1;
        foreach ($maps as $depth => $map) {
            foreach ($map as [$source, $target, $length]) {
                // Merge with existing ranges
                $newRange = new Range($source, $target, $length, $depth);
                for ($r = 0; $r < count($ranges); $r++) {
                    $range = $ranges[$r];
                    if ($range->depth === $newRange->depth) {
                        continue; // already replaced this time around!
                    }
                    // Split the range when there is an intersection!
                    if (
                        ($newRange->source >= $range->target && $newRange->source <= $range->targetEnd)
                        || ($newRange->sourceEnd >= $range->target && $newRange->sourceEnd <= $range->targetEnd)
                        || ($newRange->source <= $range->targetEnd && $newRange->sourceEnd >= $range->targetEnd)
                    ) {
                        $splitRanges = $this->splitRanges($range, $newRange);
                        array_splice($ranges, $r, 1, $splitRanges);
                        $r += count($splitRanges) - 1;
                    }
                }
                // Gap to last
                if ($source > $maxSource + 1) {
                    // Create range from last range to start of this
                    $ranges[] = new Range($maxSource + 1, $maxSource + 1, $source - $maxSource - 1, $depth);
                    // And add the new range like there's no tomorrow
                    $ranges[] = new Range($source, $target, $length, $depth);
                    $maxSource = $source + $length - 1;
                    // We can't continue to loop here because reasons
                }
                // Gap after last
                if ($newRange->sourceEnd > $maxSource) {
                    $offset = $maxSource + 1 - $newRange->source;
                    $ranges[] = new Range($maxSource + 1, $newRange->target + $offset, $newRange->sourceEnd - $maxSource, $depth);
                }
                // Update max source only here
                $maxSource = max($maxSource, $newRange->sourceEnd);
            }
        }
        return $ranges;
    }

    protected function splitRanges(Range $range, Range $newRange): array
    {
        $splitRanges = [];

        // Existing range ends
        if ($newRange->source > $range->target) {
            $splitRanges[] = new Range($range->source, $range->target, $newRange->source - $range->target, $range->depth);
        }

        // New range
        $start = max($newRange->source, $range->target);
        $end = min($newRange->sourceEnd, $range->targetEnd);
        $offset = $start - $range->target;
        $sourceStart = $range->source + $offset;
        $targetOffset = $start - $newRange->source;
        $addedRange = new Range($sourceStart, $newRange->target + $targetOffset, $end - $start + 1, $newRange->depth);
        $splitRanges[] = $addedRange;

        // Existing range ends
        if ($newRange->sourceEnd < $range->targetEnd) {
            $start = $addedRange->sourceEnd + 1;
            $offset = $start - $range->source;
            $splitRanges[] = new Range($start, $range->target + $offset, $range->targetEnd - $newRange->sourceEnd, $range->depth);
        }

        return $splitRanges;
    }

    public function getSeeds(): array
    {
        return $this->seeds;
    }

    public function getLocation(int $seed): int
    {
        foreach ($this->map as $range) {
            if ($range->contains($seed)) {
                return $range->getTargetValue($seed);
            }
        }
        return $seed;
    }

    public function getLowestLocationInRange(int $start, int $range): int
    {
        $min = PHP_INT_MAX;
        $end = $start + $range - 1;
        $seed = $start;
        while (true) {
            foreach ($this->map as $range) {
                if ($range->contains($seed)) {
                    $min = min($range->getTargetValue($seed), $min);
                    // skip to end of range
                    if ($range->sourceEnd >= $end) {
                        break 2;
                    }
                    $seed = $range->sourceEnd + 1;
                    continue 2;
                }
            }
        }
        return $min;
    }

    protected function createReverseMap(array $map): array
    {
        $reverseMap = array_map(fn(array $values) => [$values[1], $values[0], $values[2]], $map);
        usort($reverseMap, $this->sortMap(...));
        return $reverseMap;
    }

    protected function sortMap(array $a, array $b): int
    {
        return $a[0] <=> $b[0];
    }
}

readonly class Range {

    public int $sourceEnd;
    public int $targetEnd;

    public function __construct(
        public int $source,
        public int $target,
        public int $length,
        public int $depth,
    ) {
        $this->sourceEnd = $this->source - 1 + $this->length;
        $this->targetEnd = $this->target - 1 + $this->length;
    }

    public function __toString(): string
    {
        return sprintf('Range %s - %s => %s - %s', $this->source, $this->sourceEnd, $this->target, $this->targetEnd);
    }

    public function contains(int $source): bool
    {
        return $source >= $this->source && $source <= $this->sourceEnd;
    }

    public function getTargetValue(int $source): int
    {
        return $this->target + $source - $this->source;
    }
}
