<?php

namespace AdventOfCode\Common\Output;

use AdventOfCode\Common\Utils\MapUtils;

/**
 * Class TextOutput
 *
 * @package AdventOfCode\Common\Output
 * @author  Ruud Seberechts
 */
class TextOutput
{
    public static function map2d(array $map): string
    {
        $output = '';
        foreach ($map as $row) {
            foreach ($row as $val) {
                $output .= is_string($val) ? $val : ($val ? '.' : '#');
            }
            $output .= "\n";
        }
        return $output;
    }

    public static function incompleteMap(
        array $partialMap,
        array $bounds,
        string $void = ' ',
        ?array $extra = null,
        bool $reverseY = false
    ): string {
        return self::map2d(MapUtils::createCompleteMap($partialMap, $bounds, $void, $extra, $reverseY));
    }
}
