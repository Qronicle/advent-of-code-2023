<?php

namespace AdventOfCode\Solutions;

use AdventOfCode\Common\Solution\AbstractSolution;

class Day05 extends AbstractSolution
{
    protected function solvePart1(): string
    {
        $almanac = new Almanac($this->rawInput);
        $conversions = ['soil', 'fertilizer', 'water', 'light', 'temperature', 'humidity', 'location'];
        $min = PHP_INT_MAX;
        foreach ($almanac->getSeeds() as $val) {
            $sourceCategory = 'seed';
            foreach ($conversions as $targetCategory) {
                $val = $almanac->getDestination($val, $sourceCategory, $targetCategory);
                $sourceCategory = $targetCategory;
            }
            $min = min($min, $val);
        }
        return $min;
    }

    protected function solvePart2(): string
    {
        $almanac = new Almanac($this->rawInput);
        $almanac->flattenMaps();
        $conversions = ['soil', 'fertilizer', 'water', 'light', 'temperature', 'humidity', 'location'];
        $min = PHP_INT_MAX;
        $seeds = $almanac->getSeeds();
        for ($i = 0; $i < count($seeds); $i += 2) {
            $start = $seeds[$i];
            $range = $seeds[$i + 1];
            echo "> $start (" . ($i+1) . '/' . (count($seeds) / 2) . ")\n";
            for ($seed = $start; $seed < $start + $range; $seed++) {
                $sourceCategory = 'seed';
                $val = $seed;
                foreach ($conversions as $targetCategory) {
                    $val = $almanac->getDestination($val, $sourceCategory, $targetCategory);
                    $sourceCategory = $targetCategory;
                }
                // echo "$seed > $val\n";
                $min = min($min, $val);
            }
            echo " > min: $min\n";
        }
        return $min;
    }
}

class Almanac
{
    /** @var int[] */
    protected array $seeds;

    /** @var array<string,array<string,array<int[]>>> */
    protected array $maps;

    public function __construct(string $content)
    {
        $parts = explode("\n\n", $content);

        // Seeds
        preg_match_all('/\d+/', array_shift($parts), $seedMatches);
        $this->seeds = $seedMatches[0];

        // Maps
        foreach ($parts as $part) {
            $map = explode("\n", $part);
            preg_match('/^([a-z]+)-to-([a-z]+) map:$/', array_shift($map), $mappedCategoryMatches);
            [, $targetCategory, $sourceCategory] = $mappedCategoryMatches;
            $map = array_map(fn(string $value) => explode(' ', $value), $map);
            usort($map, $this->sortMap(...));
            // $this->maps[$sourceCategory][$targetCategory] = $map;
            $this->maps[$targetCategory][$sourceCategory] = $this->createReverseMap($map);
        }
    }

    public function flattenMaps(): void
    {
        $map1 = $this->maps['seed']['soil'];
        $map2 = $this->maps['soil']['fertilizer'];
        $limits = [];
        foreach ($map1 as $values) {
            $range = new Range($values[0], $values[1], $values[2]);
            $limits[] = $range;
        }
        $ranges = $this->cleanupRanges($limits);
        // dump(implode("\n", $ranges));
        $minRange = 0;
        foreach ($map2 as [$source, $target, $length]) {
            $newRange = new Range($source, $target, $length);
            // merge range into
            for ($r = $minRange; $r < count($ranges); $r++) {
                $range = $ranges[$r];
                if (
                    ($newRange->source >= $range->target && $newRange->source <= $range->targetEnd)
                    || ($newRange->sourceEnd >= $range->target && $newRange->sourceEnd <= $range->targetEnd)
                ) {
                    $splitRanges = $this->splitRanges($range, $newRange);
                    dump($this->dump($splitRanges));
                    array_splice($ranges, $r, 1, $splitRanges);
                    $minRange = $r + 1;
                    $r += count($splitRanges) - 1;
                    dump('-----', $this->dump($ranges), '-----');
                }
            }
            die;
        }
        die;
        dump($this->dump($ranges));
        $ranges = $this->cleanupRanges($ranges);
        dump('---');
        dd($this->dump($ranges));
    }

    protected function dump(array $ranges): string
    {
        return implode("\n", $ranges);
    }

    protected function splitRanges(Range $range, Range $newRange): array
    {
        dump($range);
        $splitRanges = [];
        if ($newRange->source <= $range->target) {
            $overLap = min($range->length, $newRange->source - $range->target + $newRange->length);
            $splitRanges[] = new Range($range->source, $newRange->target, $overLap);
        } else {
            dd('existing part be higher');
        }
        if ($range->targetEnd > $newRange->sourceEnd) {
            $length = $range->targetEnd - $newRange->sourceEnd;
            $offset = $range->length - $length;
            $splitRanges[] = new Range($range->source + $offset, $range->target + $offset, $length);
        }
        return $splitRanges;
    }

    /**
     * @param Range[] $ranges
     * @return Range[]
     */
    protected function cleanupRanges(array $ranges): array
    {
        usort($ranges, $this->sortRanges(...));
        $cleanedRanges = [];
        $max = 0;
        foreach ($ranges as $range) {
            if ($range->target > $max + 1) {
                $cleanedRanges[] = new Range($max, $max, $range->target - $max);
            }
            $cleanedRanges[] = $range;
            $max = max($max, $range->targetEnd);
        }
        return $cleanedRanges;
    }

    protected function sortRanges(Range $a, Range $b): int
    {
        return $a->target <=> $b->target;
    }

    public function getSeeds(): array
    {
        return $this->seeds;
    }

    public function getDestination(int $source, string $sourceCategory, string $targetCategory): int
    {
        $map = $this->maps[$sourceCategory][$targetCategory];
        foreach ($map as $values) {
            if ($values[0] > $source) {
                continue;
            }
            if ($values[0] + $values[2] > $source) {
                $target = $source - $values[0] + $values[1];
                // echo "$source $sourceCategory > $target $targetCategory\n";
                return $target;
            }
        }
        // echo "$source $sourceCategory > $source $targetCategory\n";
        return $source;
    }

    protected function sortMap(array $a, array $b): int
    {
        return $a[0] <=> $b[0];
    }

    protected function createReverseMap(array $map): array
    {
        $reverseMap = array_map(fn(array $values) => [$values[1], $values[0], $values[2]], $map);
        usort($reverseMap, $this->sortMap(...));
        return $reverseMap;
    }
}

readonly class Range {

    public int $sourceEnd;
    public int $targetEnd;

    public function __construct(
        public int $source,
        public int $target,
        public int $length,
    ) {
        $this->sourceEnd = $this->source + $this->length - 1;
        $this->targetEnd = $this->target + $this->length - 1;
    }

    public function __toString(): string
    {
        return sprintf('Range %s - %s (%s - %s)', $this->target, $this->targetEnd, $this->source, $this->sourceEnd);
    }
}
