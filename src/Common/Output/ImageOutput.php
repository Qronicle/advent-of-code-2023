<?php

namespace AdventOfCode\Common\Output;

use Exception;

/**
 * Class ImageOutput
 *
 * @package AdventOfCode\Common\Output
 * @author  Ruud Seberechts
 */
class ImageOutput
{
    public static function strtoimg(string $string, string $filename, int $pixelSize = 5, array $colorMap = []): void
    {
        if (!file_exists(dirname($filename))) {
            mkdir(dirname($filename), 0777, true);
        }
        $string = trim($string);
        $lines = explode("\n", $string);
        if (!$lines) {
            throw new Exception('strtoimg: no input given');
        }
        $img = imagecreate(strlen($lines[0]) * $pixelSize, count($lines) * $pixelSize);
        $colors = [];
        foreach ($lines as $y => $line) {
            $line = str_split($line);
            foreach ($line as $x => $colorIndex) {
                if (isset($colors[$colorIndex])) {
                    $color = $colors[$colorIndex];
                } else {
                    if (isset($colorMap[$colorIndex])) {
                        $color = imagecolorallocate($img, $colorMap[$colorIndex][0], $colorMap[$colorIndex][1], $colorMap[$colorIndex][2]);
                    } else {
                        $color = imagecolorallocate($img, round($colorIndex * 25), round($colorIndex * 25), round($colorIndex * 25));
                    }
                    $colors[$colorIndex] = $color;
                }
                imagefilledrectangle($img, $x * $pixelSize, $y * $pixelSize, $x * $pixelSize + $pixelSize, $y * $pixelSize + $pixelSize, $color);
            }
        }
        imagepng($img, $filename);
    }

    public static function map(array $map, string $filename, int $pixelSize, array $colorMap): void
    {
        if (!file_exists(dirname($filename))) {
            mkdir(dirname($filename), 0777, true);
        }
        $width = count(array_first($map));
        $height = count($map);
        $img = imagecreate($width * $pixelSize, $height * $pixelSize);
        $colors = [];

        // Find x & y offset
        $xOffset = $yOffset = 0;
        foreach ($map as $y => $row) {
            $yOffset = -$y;
            foreach ($row as $x => $value) {
                $xOffset -= $x;
                break 2;
            }
        }

        foreach ($map as $y => $row) {
            foreach ($row as $x => $value) {
                if (isset($colors[$value])) {
                    $color = $colors[$value];
                } else {
                    if (isset($colorMap[$value])) {
                        $color = imagecolorallocate($img, $colorMap[$value][0], $colorMap[$value][1], $colorMap[$value][2]);
                    } else {
                        throw new Exception("Unmapped color value '$value' found");
                    }
                    $colors[$value] = $color;
                }
                imagefilledrectangle(
                    $img,
                    ($x + $xOffset) * $pixelSize,
                    ($y + $yOffset) * $pixelSize,
                    ($x + $xOffset) * $pixelSize + $pixelSize,
                    ($y + $yOffset) * $pixelSize + $pixelSize,
                    $color
                );
            }
        }
        imagepng($img, $filename);
    }

    public static function pngSequenceToGif(string $dir, string $filename): void
    {
        $files = glob("$dir/*.png");
        $last = array_last($files);
        exec("convert -delay 2 $dir/*.png -delay 200 $last $dir/$filename");
    }
}
