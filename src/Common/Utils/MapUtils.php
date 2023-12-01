<?php

namespace AdventOfCode\Common\Utils;

/**
 * Class MapUtils
 *
 * @package AdventOfCode\Common\Utils
 * @author  Ruud Seberechts
 */
class MapUtils
{
    public static function createCompleteMap(
        array $partialMap,
        array $bounds,
        string $void = ' ',
        ?array $extra = null,
        bool $reverseY = false
    ): array {
        $l = $bounds['l'] ?? $bounds['x'] ?? 0;
        $r = $bounds['r'] ?? $l + $bounds['w'];
        $w = $bounds['w'] ?? $r - $l + 1;
        $t = $bounds['t'] ?? $bounds['y'] ?? 0;
        $b = $bounds['b'] ?? $t + $bounds['h'];
        $h = $bounds['h'] ?? $b - $t + 1;
        $map = array_fill($t, $h, array_fill($l, $w, $void));
        foreach ($partialMap as $y => $row) {
            foreach ($row as $x => $value) {
                $map[$y][$x] = is_string($value) ? $value : ($value ? '#' : '.');
            }
        }
        foreach ($extra ?? [] as $char => $extraInfo) {
            if (is_numeric(key($extraInfo))) {
                $extraInfo = ['coords' => $extraInfo];
            }
            $char = $extraInfo['char'] ?? $char;
            $coords = $extraInfo['coords'];
            $offset = $extraInfo['offset'] ?? [0, 0];
            foreach ($coords as $crds) {
                $map[$crds[1] + $offset[1]][$crds[0] + $offset[0]] = $char;
            }
        }
        if ($reverseY) {
            $map = array_reverse($map);
        }
        return $map;
    }
}
