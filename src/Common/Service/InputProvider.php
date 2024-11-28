<?php

namespace AdventOfCode\Common\Service;

use RuntimeException;

class InputProvider
{
    protected string $rawInputDir;
    protected string $encryptedInputDir;
    protected string $inputVariationsDir;
    protected string $keyPath;

    public function __construct()
    {
        $this->rawInputDir = BASE_DIR . '/var/input/raw';
        $this->encryptedInputDir = BASE_DIR . '/var/input/encrypted';
        $this->inputVariationsDir = BASE_DIR . '/var/input/variations';
        $this->keyPath = BASE_DIR . '/var/input/key';
    }

    public function getInputForDay(int $day, ?string $variation = null): string
    {
        $dayName = 'day' . str_pad((string) $day, 2, '0', STR_PAD_LEFT);

        // When a variation is requested, this always comes from local storage
        if ($variation) {
            $rawPath = "$this->inputVariationsDir/$dayName-$variation.txt";
            return $this->readFile($rawPath);
        }

        // Try raw input
        $rawPath = $this->getRawInputPath($day);
        if (file_exists($rawPath)) {
            return $this->readFile($rawPath);
        }

        // Try encrypted path
        $encryptedPath = "$this->encryptedInputDir/$dayName.bin";
        if (file_exists($encryptedPath)) {
            $encryptedContent = $this->readFile($encryptedPath);
            $content = $this->decrypt($encryptedContent);
            $this->writeFile($rawPath, $content);
            return $content;
        }

        $content = $this->downloadInputFile($day);
        $encryptedContent = $this->encrypt($content);
        $this->writeFile($encryptedPath, $encryptedContent);
        $this->writeFile($rawPath, $content);
        return $content;
    }

    public function getRawInputPath(int $day): string
    {
        $dayName = 'day' . str_pad((string) $day, 2, '0', STR_PAD_LEFT);
        return "$this->rawInputDir/$dayName.txt";
    }

    protected function downloadInputFile(int $day): string
    {
        $year = $_ENV['YEAR'] ?? date('Y');
        $url = "https://adventofcode.com/$year/day/$day/input";
        $ctx = stream_context_create([
            'http' => [
                'header' => 'Cookie: session=' . ($_ENV['AOC_SESSION_ID'] ?? ''),
            ],
        ]);
        $content = file_get_contents($url, context: $ctx);
        if ($content === false) {
            throw new RuntimeException("Could not download input file '$url'");
        }
        return trim($content);
    }

    public function encrypt(string $message): string
    {
        [$key, $nonce] = $this->getKeyAndNonce();
        $paddedMessage = sodium_pad($message, 16);
        return sodium_crypto_secretbox($paddedMessage, $nonce, $key);
    }

    public function decrypt(string $message): string
    {
        [$key, $nonce] = $this->getKeyAndNonce();
        $paddedMessage = sodium_crypto_secretbox_open($message, $nonce, $key);
        return sodium_unpad($paddedMessage, 16);
    }

    protected function getKeyAndNonce(): array
    {
        if (!file_exists($this->keyPath)) {
            $data = [
                sodium_crypto_secretbox_keygen(),
                random_bytes(SODIUM_CRYPTO_SECRETBOX_NONCEBYTES),
            ];
            $this->writeFile($this->keyPath, json_encode(array_map('base64_encode', $data)));
            return $data;
        }
        return array_map('base64_decode', json_decode($this->readFile($this->keyPath)));
    }

    protected function readFile(string $path): string
    {
        $contents = file_get_contents($path);
        if ($contents === false) {
            throw new RuntimeException("Could not read input file '$path'");
        }
        return $contents;
    }

    protected function writeFile(string $path, string $content): void
    {
        $dir = dirname($path);
        if (!file_exists($dir)) {
            mkdir($dir, recursive: true);
        }
        $result = file_put_contents($path, $content);
        if ($result === false) {
            throw new RuntimeException("Could not write file '$path'");
        }
    }
}
