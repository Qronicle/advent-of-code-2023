<?php

namespace AdventOfCode\Solutions;

use AdventOfCode\Common\Solution\AbstractSolution;

class Day24 extends AbstractSolution
{
    protected function solvePart1(): string
    {
        $hails = $this->getHailStones();

        $numHails = count($hails);
        $limit = $numHails <10 ? [7, 27] : [200000000000000, 400000000000000];
        $count = 0;
        for ($i = 0; $i < $numHails - 1; $i++) {
            for ($ii = $i + 1; $ii < $numHails; $ii++) {
                $intersection = $hails[$i]->intersect2d($hails[$ii]);
                if (!$intersection) {
                    continue;
                }
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
        $stones = $this->getHailStones();
        $numStones = count($stones);

        // https://github.com/MarkSinke/aoc2023/blob/main/day24.go#L292

        $rootPosition = $stones[0]->position;
        $rootVelocity = $stones[0]->velocity;

        // translate everything into a system with stones[0] as the frame of reference
        for ($i = 0; $i < $numStones; $i++) {
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

        [$p2, $t2] = $this->linePlaneIntersection($normal, $stones[2]);
        [$p3, $t3] = $this->linePlaneIntersection($normal, $stones[3]);
        dd('!!!', $p3, $t3);

        $tDiff = $t2 - $t3;
        $dir = $p2->sub($p3)->divide($tDiff);
        $pos = $p2->sub($dir->multiply($t2));

        $stone = new Ray($pos->add($rootPosition), $dir->add($rootVelocity));

        return $stone->position->x + $stone->position->y + $stone->position->z;

        // 1098236662065353 = too high
        // 1013832282296690 = too high
    }

    /**
     * @return array{Point3, int}
     */
    protected function linePlaneIntersection(Point3 $normal, Ray $ray)
    {
        // https://en.wikipedia.org/wiki/Line%E2%80%93plane_intersection
        $a = (new Point3(0,0,0))->sub($ray->position)->dotProduct($normal);
        $b = $ray->velocity->dotProduct($normal);
        $t = intdiv($a, $b);
        dump($t);
        $p = $ray->position->add($ray->velocity->multiply($t));
        return [$p, $t];
    }

    /** @return Ray[] */
    protected function getHailStones(): array
    {
        $hails = [];
        foreach ($this->getInputLines() as $line) {
            [$pos, $vel] = explode(' @ ', $line);
            $hails[] = new Ray(
                new Point3(...explode(', ', $pos)),
                new Point3(...explode(', ', $vel)),
            );
        }
        return $hails;
    }
}

class Ray
{
    public Vector3 $direction;

    public function __construct(
        public Point3 $position,
        public Point3 $velocity,
    ) {
        $this->direction = $this->velocity->unit2d();
    }

    public function intersect2d(Ray $ray): ?Vector3
    {
        $dx = $ray->position->x - $this->position->x;
        $dy = $ray->position->y - $this->position->y;
        $det = $ray->direction->x * $this->direction->y - $ray->direction->y * $this->direction->x;
        if ($det == 0) {
            return null;
        }
        $u = ($dy * $ray->direction->x - $dx * $ray->direction->y) / $det;
        $v = ($dy * $this->direction->x - $dx * $this->direction->y) / $det;
        if ($u < 0 || $v < 0) {
            return null;
        }

        return new Vector3(
            $this->position->x + $u * $this->direction->x,
            $this->position->y + $u * $this->direction->y,
            0,
        );
    }

    public function positionAt(int $t): Point3
    {
        return new Point3(
            $this->position->x + $t * $this->velocity->x,
            $this->position->y + $t * $this->velocity->y,
            $this->position->z + $t * $this->velocity->z,
        );
    }
}

class Point3
{
    public function __construct(
        public int $x,
        public int $y,
        public int $z,
    ) {
    }

    public function fakeUnit(): Vector3
    {
        $d = greatest_common_divisor($this->x, $this->y);
        return new Vector3($this->x / $d, $this->y / $d, $this->z / $d);
    }

    public function unit2d(): Vector3
    {
        $m = sqrt(($this->x ** 2) + ($this->y ** 2));
        return new Vector3($this->x / $m, $this->y / $m, $this->z / $m);
    }

    public function sub(Point3 $point): Point3
    {
        return new Point3(
            $this->x - $point->x,
            $this->y - $point->y,
            $this->z - $point->z,
        );
    }

    public function add(Point3 $point): Point3
    {
        return new Point3(
            $this->x + $point->x,
            $this->y + $point->y,
            $this->z + $point->z,
        );
    }

    public function multiply(Point3|float|int $point): Point3
    {
        if ($point instanceof Point3) {
            return new Point3(
                $this->x * $point->x,
                $this->y * $point->y,
                $this->z * $point->z,
            );
        } else {
            dump($this->x * $point, $this->y * $point, $this->z * $point);
            return new Point3(
                $this->x * $point,
                $this->y * $point,
                $this->z * $point,
            );
        }
    }

    public function divide(int $n): Point3
    {
        return new Point3(
            intdiv($this->x, $n),
            intdiv($this->y, $n),
            intdiv($this->z, $n),
        );
    }

    public function equals(Point3 $point): bool
    {
        return
            $this->x === $point->x &&
            $this->y === $point->y &&
            $this->z === $point->z
        ;
    }

    public function dotProduct(Point3 $point): int
    {
        $p = $this->x * $point->x + $this->y * $point->y + $this->z * $point->z;
        dump(number_format($p, 5));
        return (int)round($p);
    }

    public function crossProduct(Point3 $point): Point3
    {
        return new Point3(
            $this->y * $point->z - $this->z * $point->y,
            $this->z * $point->x - $this->x * $point->z,
            $this->x * $point->y - $this->y * $point->x,
        );
    }

    public function __toString(): string
    {
        return "$this->x,$this->y,$this->z";
    }
}

class Vector3
{
    public function __construct(
        public float $x,
        public float $y,
        public float $z,
    ) {
    }
}