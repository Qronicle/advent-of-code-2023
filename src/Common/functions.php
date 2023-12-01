<?php

if (!function_exists('gmp_gcd')) {
    function greatest_common_divisor(int $n, int $m): int
    {
        $n = abs($n);
        $m = abs($m);
        if ($n == 0) return $m;
        if ($m == 0) return $n;
        return $n > $m
            ? greatest_common_divisor($m, $n % $m)
            : greatest_common_divisor($n, $m % $n);
    }
} else {
    function greatest_common_divisor(int $n, int $m): int
    {
        return gmp_intval(gmp_gcd($n, $m));
    }
}

if (!function_exists('gmp_lcm')) {
    function least_common_multiple(int $n, int $m): int
    {
        $x = $n;
        for ($y = 0; ; $x += $n) {
            while ($y < $x) {
                $y += $m;
            }
            if ($x == $y) {
                break;
            }
        }
        return $x;
    }
} else {
    function least_common_multiple(int $n, int $m): int
    {
        return gmp_intval(gmp_lcm($n, $m));
    }
}

function dd()
{
    foreach (func_get_args() as $arg) {
        print_r($arg);
        echo "\n";
    }
    die;
}

function dump()
{
    foreach (func_get_args() as $arg) {
        print_r($arg);
        echo "\n";
    }
}

function array_first(array $array) {
    return reset($array);
}

function array_last(array $array) {
    return end($array);
}

function mod(int $n1, int $n2) {
    $m = $n1 % $n2;
    if ($m < 0) {
        $m += $n2;
    }
    return $m;
}