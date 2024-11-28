<?php

namespace AdventOfCode\Common\Command;

use AdventOfCode\Common\Service\InputProvider;
use AdventOfCode\Common\Solution\AbstractSolution;
use AdventOfCode\Common\Solution\SolutionDto;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RunSolutionCommand extends Command
{
    protected function configure(): void
    {
        $this
            ->setName('run')
            ->setDescription('Run a single solution')
            ->addArgument('day', InputArgument::REQUIRED, 'Day and part (eg. 21.2)')
            ->addArgument('inputVariation', InputArgument::OPTIONAL, 'Input variation');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $dayParts = explode('.', $input->getArgument('day'));
        $day = $dayParts[0];
        $part = $dayParts[1] ?? 1;
        if ($day < 1 || $day > 25) {
            $output->writeln('<error>Day needs to be a value of 1 to 25');
        }
        if ($part < 1 || $part > 2) {
            $output->writeln('<error>Part needs to be a value of 1 or 2');
        }
        $paddedDay = str_pad($day, 2, '0', STR_PAD_LEFT);

        $inputProvider = new InputProvider();
        $inputContents = $inputProvider->getInputForDay($day, $input->getArgument('inputVariation'));

        $solutionClass = "AdventOfCode\Solutions\Day$paddedDay";
        $solver = new $solutionClass();
        assert($solver instanceof AbstractSolution);

        $solution = new SolutionDto();
        $solution->solve($solver->solve($part, $inputContents));

        $output->writeln('<info>Solution:</info>');
        $output->writeln($solution->result);
        $output->writeln('');
        $output->writeln('<info>Duration:</info> ' . number_format($solution->duration, 5, '.', '') . ' sec');
        $output->writeln('<info>Memory:</info> ' . number_format($solution->memory, 2, '.', '') . ' MB');

        return 0;
    }
}
