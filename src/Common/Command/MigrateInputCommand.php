<?php

namespace AdventOfCode\Common\Command;

use AdventOfCode\Common\Service\InputProvider;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MigrateInputCommand extends Command
{
    protected function configure(): void
    {
        $this
            ->setName('input:migrate')
            ->setDescription('Migrate plain text input to new input folder structure');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $inputDir = BASE_DIR . '/var/input';
        $rawDir = $inputDir . '/raw';
        $encryptedDir = $inputDir . '/encrypted';
        $variationsDir = $inputDir . '/variations';
        if (!file_exists($rawDir)) {
            mkdir($rawDir);
        }

        // Move all files to raw or variations dirs
        $files = scandir($inputDir);
        foreach ($files as $file) {
            if (!str_ends_with($file, '.txt')) {
                continue;
            }
            if (str_contains($file, '-')) {
                // Move to variations
                if (!file_exists($variationsDir)) {
                    mkdir($variationsDir);
                }
                rename("$inputDir/$file", "$variationsDir/$file");
            } else {
                rename("$inputDir/$file", "$rawDir/$file");
            }
        }

        // Encrypt all raw files
        $inputProvider = new InputProvider();
        $files = scandir($rawDir);
        foreach ($files as $file) {
            if (!str_ends_with($file, '.txt')) {
                continue;
            }
            $encrypted = $inputProvider->encrypt(file_get_contents("$rawDir/$file"));
            file_put_contents($encryptedDir . '/' . substr_replace($file, '.bin', -4, 4), $encrypted);
        }

        return 0;
    }
}
