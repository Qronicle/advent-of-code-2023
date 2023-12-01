# Advent of Code 2023
PHP Solutions

## Installation
```
composer install
```

## Running a solution
```
./run 5.1
```
This will run day 5 part 1 using the `var/input/day05.txt` file as input

## Running a solution with a test file
```
./run 11.2 test
```
This will run day 11 part 2 using the `var/input/day11-test.txt` file as input.
Note that 'test' can be any string, as long as an input file exists for that day, with that suffix.

## Creating a solution class for a specific day
```
./create 5
```
This will create a `AdventOfCode\Solutions\Day05` class, and empty input files in `var/input`.
