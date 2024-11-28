<?php

namespace AdventOfCode\Common\Command;

use AdventOfCode\Common\Service\InputProvider;
use AdventOfCode\Common\Solution\AbstractSolution;
use AdventOfCode\Common\Solution\SolutionDto;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CreateSolutionCommand extends Command
{
    protected function configure(): void
    {
        $this
            ->setName('create')
            ->setDescription('Create the solution class and download the input for a specific day')
            ->addArgument('day', InputArgument::REQUIRED, 'Day Solution class to create (eg. 21)');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $day = (int) $input->getArgument('day');
        if ($day < 1 || $day > 25) {
            $output->writeln('<error>Day needs to be a value of 1 to 25');
        }
        $paddedDay = str_pad($day, 2, '0', STR_PAD_LEFT);

        // Create solution class
        $output->write(' > Creating class...');
        if ($this->createClass($paddedDay)) {
            $output->writeln(' <info>Done</info>');
        } else {
            $output->writeln(' <error>Skipped</error>');
        }

        // Download input
        $output->write(' > Downloading input...');
        $inputProvider = new InputProvider();
        $inputProvider->getInputForDay($day);
        $output->writeln(' <info>Done</info>');

        // Open class and input file
        $files = [
            $inputProvider->getRawInputPath($day),
            $this->getSolutionClassPath($paddedDay),
        ];
        exec('open -na "PhpStorm.app" --args "' . implode('" "', $files) . '"');

        return 0;
    }

    protected function getSolutionClassPath(string $day): string
    {
        return BASE_DIR . "/src/Solutions/Day$day.php";
    }

    protected function createClass(string $day): bool
    {
        $filename = $this->getSolutionClassPath($day);
        if (file_exists($filename)) {
            return false;
        }

        $template = file_get_contents(BASE_DIR . "/src/Common/Resources/solution-template.txt");
        $fileContents = str_replace('{{day_nr}}', $day, $template);
        file_put_contents($filename, $fileContents);

        return true;
    }
}
