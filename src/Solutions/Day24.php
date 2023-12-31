<?php

namespace AdventOfCode\Solutions;

use AdventOfCode\Common\Solution\AbstractSolution;

class Day24 extends AbstractSolution
{
    protected function solvePart1(): string
    {
        /** @var Ray2d[] $hails */
        $hails = $this->getHailStones(true);

        $numHails = count($hails);
        $limit = $numHails < 10 ? [7, 27] : [200000000000000, 400000000000000];
        $count = 0;
        for ($i = 0; $i < $numHails - 1; $i++) {
            for ($ii = $i + 1; $ii < $numHails; $ii++) {
                $intersection = $hails[$i]->intersect($hails[$ii]);
                if (!$intersection) {
                    continue;
                }
                // check in future
                if (
                    sign($intersection->x - $hails[$i]->position->x) !== sign($hails[$i]->velocity->x)
                    || sign($intersection->x - $hails[$ii]->position->x) !== sign($hails[$ii]->velocity->x)
                ) {
                    continue;
                }
                // check within limits
                if ($intersection->x < $limit[0] || $intersection->y < $limit[0] || $intersection->x > $limit[1] || $intersection->y > $limit[1]) {
                    continue;
                }
                $count++;
            }
        }
        return $count;
    }

    protected function solvePart2(): string
    {
        // We need a lot of precision for this one
        bcscale(60);

        $stones = $this->getHailStones();

        // https://github.com/MarkSinke/aoc2023/blob/main/day24.go#L292

        $rootPosition = $stones[0]->position;
        $rootVelocity = $stones[0]->velocity;

        // translate all used hail into a system with stones[0] as the frame of reference
        for ($i = 0; $i <= 3; $i++) {
            $stones[$i]->position = $stones[$i]->position->sub($rootPosition);
            $stones[$i]->velocity = $stones[$i]->velocity->sub($rootVelocity);
        }

        // the first hail stone is at (0,0,0) and does not move, so the rock needs to pass through (0,0,0)
        // stones[1] follows a trajectory that the rock needs to intersect with. This means that it intersects
        // somewhere in the plane defined by the origin (rootPosition aka 0,0,0) and any 2 points on that line
        // we can compute the unit vector of that plane by taking the vectors from the origin to t=0 and t=1
        // after that, we compute two intersections for stones[2] and stones[3] and work out dir and p0 from
        // there

        // this normal defines the plane between (0,0,0) and two stone[1] points over time
        $normal = $stones[1]->position->crossProduct($stones[1]->positionAt(1));

        [$p2, $t2] = $stones[2]->planeIntersection($normal);
        [$p3, $t3] = $stones[3]->planeIntersection($normal);

        $tDiff = $t2 - $t3;
        $dir = $p2->sub($p3)->divide($tDiff);
        $pos = $p2->sub($dir->multiply($t2));

        $stone = new Ray($pos->add($rootPosition), $dir->add($rootVelocity));

        return (int)bcadd(bcadd($stone->position->x, $stone->position->y), $stone->position->z);
    }

    /** @return Ray[] */
    protected function getHailStones(bool $ray2d = false): array
    {
        $hails = [];
        foreach ($this->getInputLines() as $line) {
            [$pos, $vel] = explode(' @ ', $line);
            $hails[] = $ray2d
                ? new Ray2d(
                    new Point(...array_map('trim', explode(', ', $pos))),
                    new Point(...array_map('trim', explode(', ', $vel))),
                )
                : new Ray(
                    new Vector3(...array_map('trim', explode(', ', $pos))),
                    new Vector3(...array_map('trim', explode(', ', $vel))),
                );
        }
        return $hails;
    }
}

// 2D //////////////////////////////////////////////////////////////////////////////////////////////////////////////////

class Ray2d
{
    public function __construct(
        public Point $position,
        public Point $velocity,
    ) {
    }

    public function intersect(Ray2d $ray): ?Point
    {
        $determinant = $this->velocity->x * $ray->velocity->y - $this->velocity->y * $ray->velocity->x;
        if ($determinant == 0) {
            return null;
        }

        $u = $this->velocity->x * $this->position->y - $this->velocity->y * $this->position->x;
        $v = $ray->velocity->x * $ray->position->y - $ray->velocity->y * $ray->position->x;

        return new Point(
            ($ray->velocity->x * $u - $this->velocity->x * $v) / $determinant,
            ($ray->velocity->y * $u - $this->velocity->y * $v) / $determinant,
        );
    }
}

class Point
{
    public function __construct(
        public float $x = 0,
        public float $y = 0,
    ) {
    }
}

// 3D //////////////////////////////////////////////////////////////////////////////////////////////////////////////////

class Ray
{
    public function __construct(
        public Vector3 $position,
        public Vector3 $velocity,
    ) {
    }

    public function positionAt(int $t): Vector3
    {
        return new Vector3(
            bcadd($this->position->x, bcmul($t, $this->velocity->x)),
            bcadd($this->position->y, bcmul($t, $this->velocity->y)),
            bcadd($this->position->z, bcmul($t, $this->velocity->z)),
        );
    }

    /**
     * @return array{Vector3, int}
     */
    public function planeIntersection(Vector3 $normal): array
    {
        // https://en.wikipedia.org/wiki/Line%E2%80%93plane_intersection
        $a = (new Vector3())->sub($this->position)->dotProduct($normal);
        $b = $this->velocity->dotProduct($normal);
        $t = bcdiv($a, $b);
        $p = $this->position->add($this->velocity->multiply($t));
        return [$p, $t];
    }
}

class Vector3
{
    public function __construct(
        public string $x = '0',
        public string $y = '0',
        public string $z = '0',
    ) {
    }

    public function unit(): Vector3
    {
        $m = bcsqrt(bcadd(bcpow($this->x, '2'), bcpow($this->y, '2')));
        return new Vector3(bcdiv($this->x, $m), bcdiv($this->y, $m), bcdiv($this->z, $m));
    }

    public function sub(Vector3 $point): Vector3
    {
        return new Vector3(
            bcsub($this->x, $point->x),
            bcsub($this->y, $point->y),
            bcsub($this->z, $point->z),
        );
    }

    public function add(Vector3 $point): Vector3
    {
        return new Vector3(
            bcadd($this->x, $point->x),
            bcadd($this->y, $point->y),
            bcadd($this->z, $point->z),
        );
    }

    public function multiply(string $val): Vector3
    {
        return new Vector3(
            bcmul($this->x, $val),
            bcmul($this->y, $val),
            bcmul($this->z, $val),
        );
    }

    public function divide(string $val): Vector3
    {
        return new Vector3(
            bcdiv($this->x, $val),
            bcdiv($this->y, $val),
            bcdiv($this->z, $val),
        );
    }

    public function dotProduct(Vector3 $point): string
    {
        $p = bcmul($this->x, $point->x);
        $p = bcadd($p, bcmul($this->y, $point->y));
        $p = bcadd($p, bcmul($this->z, $point->z));
        return $p;
    }

    public function crossProduct(Vector3 $point): Vector3
    {
        return new Vector3(
            bcsub(bcmul($this->y, $point->z), bcmul($this->z, $point->y)),
            bcsub(bcmul($this->z, $point->x), bcmul($this->x, $point->z)),
            bcsub(bcmul($this->x, $point->y), bcmul($this->y, $point->x)),
        );
    }
}
