<?php

namespace AdventOfCode\Common\Command;

use AdventOfCode\Common\Service\InputProvider;
use AdventOfCode\Common\Solution\AbstractSolution;
use AdventOfCode\Common\Solution\SolutionDto;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @phpstan-type Benchmark array<int, array{duration: float, memory: float}>
 * @phpstan-type Benchmarks array<int, Benchmark>

 */
class BenchmarkSolutionsCommand extends Command
{
    protected function configure(): void
    {
        $this
            ->setName('benchmark')
            ->setDescription('Benchmark a single or all solutions')
            ->addArgument('day', InputArgument::OPTIONAL, 'Day');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $benchmarks = $this->getFromReadme();
        $startDay = 1;
        $endDay = 25;
        if ($day = (int) $input->getArgument('day')) {
            $startDay = $endDay = $day;
        }
        for ($day = $startDay; $day <= $endDay; ++$day) {
            $output->write(" > Benchmarking <info>day $day</info>...");
            $benchmarks[$day] = $this->benchmarkDay($day);
            if ($output->isDebug()) {
                dump($benchmarks[$day]);
            }
            $output->writeln('');
        }
        ksort($benchmarks);
        $this->writeToReadme($benchmarks);
        return 0;
    }

    /**
     * @param int $day
     * @return Benchmark
     */
    protected function benchmarkDay(int $day): array
    {
        $bench = [];
        for ($part = 1; $part <= 2; ++$part) {
            $start = microtime(true);
            for ($i = 0; $i < 10; $i++) {
                exec("bin/console run $day.$part", $output);
                foreach ($output as $line) {
                    if (preg_match('/^([a-z]+): ([0-9.]+) ([a-z]+)$/i', $line, $matches)) {
                        $bench[$part][strtolower($matches[1])][$i] = (float) $matches[2];
                    }
                }
                // No benchmarks to be had or timeout reached
                if (!$bench || microtime(true) - $start > 10) {
                    break;
                }
            }
        }
        foreach ($bench as $part => $propBench) {
            foreach ($propBench as $prop => $benchmarks) {
                $bench[$part][$prop] = count($benchmarks) > 1 ? min(...$benchmarks) : $benchmarks[0];
            }
        }
        return $bench;
    }

    /**
     * @return Benchmarks
     */
    protected function getFromReadme(): array
    {
        $readmeLines = explode("\n", file_get_contents(BASE_DIR . '/README.md'));
        $header = false;
        $benchmarks = [];
        foreach ($readmeLines as $line) {
            if (str_starts_with($line, '|')) {
                if (!$header) {
                    $header = true;
                    continue;
                }
                $cols = array_map('trim', explode('|', trim($line, '| ')));
                if (trim($cols[0], ':-') === '') {
                    continue; // table header separation line
                }
                if (!is_numeric($cols[0])) {
                    continue; // total row!
                }
                $day = (int) $cols[0];
                for ($part = 1; $part <= 2; ++$part) {
                    if (preg_match('/([0-9.]+) sec<br>([0-9.]+) MB/i', $cols[$part], $matches)) {
                        $benchmarks[$day][$part]['duration'] = (float) $matches[1];
                        $benchmarks[$day][$part]['memory'] = (float) $matches[2];
                    }
                }
            } elseif ($header) {
                break;
            }
        }
        return $benchmarks;
    }

    /**
     * @param Benchmarks $benchmarks
     * @return void
     */
    protected function writeToReadme(array $benchmarks): void
    {
        // Create table array (and calculate totals)
        $table = [['Day', 'Part 1', 'Part 2', 'Total']];
        $totals = [1 => ['duration' => 0, 'memory' => 0], 2 => ['duration' => 0, 'memory' => 0], 3 => ['duration' => 0, 'memory' => 0]];
        foreach ($benchmarks as $day => $parts) {
            $row = [$day];
            $durations = [];
            $memories = [];
            for ($part = 1; $part <= 2; ++$part) {
                $bench = $parts[$part] ?? [];
                if (isset($bench['duration'])) {
                    $durations[] = $bench['duration'];
                    $memories[] = $bench['memory'];
                    $totals[$part]['duration'] += $bench['duration'];
                    $totals[$part]['memory'] += $bench['memory'];
                    $row[] = $this->formatDuration($bench['duration']) . '<br>' . $this->formatMemory($bench['memory']);
                } else {
                    $row[] = '-';
                }
            }
            if (count($durations) === 2) {
                $totals[3]['duration'] += array_sum($durations);
                $totals[3]['memory'] += array_sum($memories);
                $row[] = $this->formatDuration(array_sum($durations)) . '<br>' . $this->formatMemory(array_sum($memories));
            } else {
                $row[] = '-';
            }
            $table[] = $row;
        }
        $table[] = [
            '**TOTAL**',
            $totals[1]['duration'] ? ($this->formatDuration($totals[1]['duration']) . '<br>' . $this->formatMemory($totals[1]['memory'])) : '',
            $totals[2]['duration'] ? ($this->formatDuration($totals[2]['duration']) . '<br>' . $this->formatMemory($totals[2]['memory'])) : '',
            $totals[3]['duration'] ? ($this->formatDuration($totals[3]['duration']) . '<br>' . $this->formatMemory($totals[3]['memory'])) : '',
        ];
        $columnSizes = [0, 0, 0, 0];
        foreach ($table as $row) {
            foreach ($row as $col => $content) {
                $columnSizes[$col] = max($columnSizes[$col], strlen($content));
            }
        }

        // Create table markdown
        $tableMd = '';
        foreach ($table as $r => $row) {
            $tableMd .= '|';
            foreach ($row as $c => $col) {
                $tableMd .= ' ' . str_pad($col, $columnSizes[$c]) . ' |';
            }
            $tableMd .= "\n";
            if ($r === 0) {
                $tableMd .= '|';
                foreach ($columnSizes as $c => $size) {
                    $content = str_repeat('-', $size - 1) . ':';
                    if ($c === 0) {
                        $content = substr_replace($content, ':', 0, 1);
                    }
                    $tableMd .= ' ' . $content . ' |';
                }
                $tableMd .= "\n";
            }
        }

        // Write to readme
        // die($tableMd);
        $readmeLines = explode("\n", file_get_contents(BASE_DIR . '/README.md'));
        $tableStart = $tableEnd = null;
        foreach ($readmeLines as $i => $line) {
            if (str_starts_with($line, '|')) {
                $tableStart ??= $i;
            } elseif ($tableStart) {
                $tableEnd = $i;
                break;
            }
        }
        array_splice($readmeLines, $tableStart, $tableEnd - $tableStart + 1, [$tableMd]);
        file_put_contents(BASE_DIR . '/README.md', implode("\n", $readmeLines));
    }

    protected function formatDuration(float $duration): string
    {
        return number_format($duration, 5, '.', '') . ' sec';
    }

    protected function formatMemory(float $memory): string
    {
        return number_format($memory, 2, '.', '') . ' MB';
    }
}
