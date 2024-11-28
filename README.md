# Advent of Code 2023 ~ PHP

## Benchmarks

| Day       | Part 1                    | Part 2                     | Total                      |
| :-------: | ------------------------: | -------------------------: | -------------------------: |
| 1         | 0.00052 sec<br>0.07 MB    | 0.00163 sec<br>0.07 MB     | 0.00215 sec<br>0.14 MB     |
| 2         | 0.00053 sec<br>0.04 MB    | 0.00072 sec<br>0.04 MB     | 0.00125 sec<br>0.08 MB     |
| 3         | 0.00537 sec<br>1.74 MB    | 0.00334 sec<br>1.84 MB     | 0.00871 sec<br>3.58 MB     |
| 4         | 0.00078 sec<br>0.52 MB    | 0.00098 sec<br>0.52 MB     | 0.00176 sec<br>1.04 MB     |
| 5         | 0.00598 sec<br>0.15 MB    | 0.00729 sec<br>0.15 MB     | 0.01327 sec<br>0.30 MB     |
| 6         | 0.00002 sec<br>0.04 MB    | 1.73398 sec<br>0.04 MB     | 1.73400 sec<br>0.08 MB     |
| 7         | 0.00406 sec<br>0.38 MB    | 0.00419 sec<br>0.38 MB     | 0.00825 sec<br>0.76 MB     |
| 8         | 0.00096 sec<br>0.48 MB    | 0.01803 sec<br>0.48 MB     | 0.01899 sec<br>0.96 MB     |
| 9         | 0.00394 sec<br>0.05 MB    | 0.00392 sec<br>0.05 MB     | 0.00786 sec<br>0.10 MB     |
| 10        | 0.00766 sec<br>3.37 MB    | 0.02443 sec<br>4.56 MB     | 0.03209 sec<br>7.93 MB     |
| 11        | 0.04187 sec<br>1.77 MB    | 0.04172 sec<br>1.77 MB     | 0.08359 sec<br>3.54 MB     |
| 12        | 0.05496 sec<br>0.10 MB    | 1.17222 sec<br>1.70 MB     | 1.22718 sec<br>1.80 MB     |
| 13        | 0.00054 sec<br>0.04 MB    | 0.00197 sec<br>0.04 MB     | 0.00251 sec<br>0.08 MB     |
| 14        | 0.00124 sec<br>0.81 MB    | 0.92910 sec<br>2.48 MB     | 0.93034 sec<br>3.29 MB     |
| 15        | 0.00283 sec<br>0.04 MB    | 0.00408 sec<br>0.29 MB     | 0.00691 sec<br>0.33 MB     |
| 16        | 0.00387 sec<br>5.23 MB    | 0.82225 sec<br>5.47 MB     | 0.82612 sec<br>10.70 MB    |
| 17        | 17.74143 sec<br>594.28 MB | 18.54809 sec<br>1687.97 MB | 36.28952 sec<br>2282.25 MB |
| 18        | 0.01761 sec<br>3.68 MB    | 0.00038 sec<br>0.20 MB     | 0.01799 sec<br>3.88 MB     |
| 19        | 0.00283 sec<br>0.22 MB    | 0.00288 sec<br>0.80 MB     | 0.00571 sec<br>1.02 MB     |
| 20        | 0.03902 sec<br>0.08 MB    | 0.14536 sec<br>0.08 MB     | 0.18438 sec<br>0.16 MB     |
| 21        | 0.00542 sec<br>1.77 MB    | 0.09372 sec<br>2.13 MB     | 0.09914 sec<br>3.90 MB     |
| 22        | 0.01558 sec<br>1.70 MB    | 0.62988 sec<br>1.82 MB     | 0.64546 sec<br>3.52 MB     |
| 23        | 0.00721 sec<br>1.82 MB    | 18.99492 sec<br>1.80 MB    | 19.00213 sec<br>3.62 MB    |
| 24        | 0.05022 sec<br>0.12 MB    | 0.00073 sec<br>0.19 MB     | 0.05095 sec<br>0.31 MB     |
| 25        | 16.30721 sec<br>6.04 MB   | 0.00000 sec<br>0.04 MB     | 16.30721 sec<br>6.08 MB    |
| **TOTAL** | 34.32166 sec<br>624.54 MB | 43.18581 sec<br>1714.91 MB | 77.50747 sec<br>2339.45 MB |

## Usage

### Installation
```
composer install
```

### Running a solution
```
bin/console run 5.1
```
This will run day 5 part 1 using the `var/input/raw/day05.txt` file as input

### Running a solution with a test file
```
bin/console run 11.2 test
```
This will run day 11 part 2 using the `var/input/variations/day11-test.txt` file as input.
Note that 'test' can be any string, as long as an input file exists for that day, with that suffix.

### Creating a solution class for a specific day
```
bin/console create 5
```
This will create a `AdventOfCode\Solutions\Day05` class, and input files in `var/input` when the AOC_SESSION_ID is 
defined by the `.env` file.

### Generating benchmarks
```
bin/console benchmark
```
This will create benchmark all solutions (run each part up to 10 times, stop after 10 seconds) and update the results in
this readme file.

It is possible to only benchmark a single day and update the readme:
```
bin/console benchmark 13
```
Note that totals will always be recalculated.