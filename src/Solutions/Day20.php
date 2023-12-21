<?php

namespace AdventOfCode\Solutions;

use AdventOfCode\Common\Solution\AbstractSolution;

class Day20 extends AbstractSolution
{
    protected function solvePart1(): string
    {
        $system = $this->createPulseSystem();
        return $system->run();
    }

    protected function solvePart2(): string
    {
        $system = $this->createPulseSystem([
            'rx' => new LowPulseDetector('rx'),
        ]);
        return $system->findLowPulse();
    }

    protected function createPulseSystem(array $defaultModules = []): PulseSystemFromHell
    {
        $modules = [
            'button' => new Broadcaster('button'),
            ...$defaultModules,
        ];
        $inputs = [];
        $outputs = [];
        foreach ($this->getInputLines() as $inputLine) {
            [$module, $outputsString] = explode(' -> ', $inputLine);
            $moduleType = substr($module, 0, 1);
            $moduleName = substr($module, 1);
            $outputNames = explode(', ', $outputsString);
            if ($moduleType === 'b') {
                $moduleName = $moduleType . $moduleName;
                $modules[$moduleName] = new Broadcaster($moduleName);
            } elseif ($moduleType === '%') {
                $modules[$moduleName] = new FlipFlop($moduleName);
            } elseif ($moduleType === '&') {
                $modules[$moduleName] = new Conjunction($moduleName);
            }
            $outputs[$moduleName] = $outputNames;
            foreach ($outputNames as $outputName) {
                $inputs[$outputName][] = $modules[$moduleName];
            }
        }
        foreach ($outputs as $i => $subOutputs) {
            foreach ($subOutputs as $ii => $outputName) {
                $module = $modules[$outputName] ?? null;
                if (!$module) {
                    $module = new Output($outputName);
                    $modules[$outputName] = $module;
                }
                $outputs[$i][$ii] = $module;
            }
        }
        foreach ($modules as $moduleName => $module) {
            $module->wire($inputs[$moduleName] ?? [], $outputs[$moduleName] ?? []);
        }
        return new PulseSystemFromHell($modules);
    }
}

class PulseSystemFromHell
{
    public function __construct(
        /** @var Broadcaster[] */
        protected array $modules,
    ) {
    }

    public function run(): int
    {
        //$states = [$this->getState() => 0];
        $numPulses = [0, 0];
        for ($i = 1; $i <= 1000; $i++) {
            $newPulses = $this->runCycle();
            $numPulses[0] += $newPulses[0];
            $numPulses[1] += $newPulses[1];
            //$state = $this->getState();
            // if (isset($states[$state])) {
            //     dd($states[$state], $i, $numPulses);
            //     return array_product($numPulses);
            // }
            //$states[$state] = $i;
        }
        return array_product($numPulses);
    }

    public function findLowPulse(): int
    {
        //$states = [$this->getState() => 0];
        $cycle = 0;
        while (!$this->modules['rx']->lowPulseDetected) {
            $this->runCycle();
            //$state = $this->getState();
            // if (isset($states[$state])) {
            //     dd($states[$state], $i, $numPulses);
            //     return array_product($numPulses);
            // }
            //$states[$state] = $i;
            if (++$cycle % 1000000 === 0) {
                dump("iteration $cycle");
            }
        }
        return $cycle;
    }

    protected function getState(): string
    {
        $state = '';
        foreach ($this->modules as $module) {
            $state .= $module->getState();
        }
        return $state;
    }

    protected function runCycle(): array
    {
        $nums = [0, 0];
        $queue = [new Pulse($this->modules['button'], $this->modules['broadcaster'], false)];
        while ($queue) {
            $newQueue = [];
            foreach ($queue as $pulse) {
                //dump($pulse->input->name . ' -' . ($pulse->pulse ? 'high' : 'low') . '-> ' . $pulse->output->name);
                $nums[(int)$pulse->pulse]++;
                $newQueue = [
                    ...$newQueue,
                    ...$pulse->output->processPulse($pulse->input, $pulse->pulse),
                ];
            }
            $queue = $newQueue;
        }
        return $nums;
    }
}

class Pulse
{
    public function __construct(
        public Broadcaster $input,
        public Broadcaster $output,
        public bool $pulse,
    ) {
    }
}

class Broadcaster
{
    /** @var Broadcaster[] */
    protected array $inputs = [];

    /** @var Broadcaster[] */
    protected array $outputs = [];

    public function __construct(public string $name)
    {
    }

    public function wire(array $inputs, array $outputs): void
    {
        $this->inputs = $inputs;
        $this->outputs = $outputs;
    }

    /**
     * @return Pulse[]
     */
    public function getOutgoingPulses(bool $pulse): array
    {
        $queue = [];
        foreach ($this->outputs as $output) {
            $queue[] = new Pulse($this, $output, $pulse);
        }
        return $queue;
    }

    /**
     * @return Pulse[]
     */
    public function processPulse(Broadcaster $broadcaster, bool $pulse): array
    {
        return $this->getOutgoingPulses($pulse);
    }

    public function getState(): string
    {
        return '';
    }
}

class FlipFlop extends Broadcaster
{
    public bool $enabled = false;

    public function processPulse(Broadcaster $broadcaster, bool $pulse): array
    {
        if (!$pulse) {
            $this->enabled = !$this->enabled;
            return $this->getOutgoingPulses($this->enabled);
        }
        return [];
    }

    public function getState(): string
    {
        return (int)$this->enabled;
    }
}

class Conjunction extends Broadcaster
{
    protected array $prevPulses = [];

    public function wire(array $inputs, array $outputs): void
    {
        parent::wire($inputs, $outputs);
        foreach ($inputs as $input) {
            $this->prevPulses[$input->name] = false;
        }
    }

    public function processPulse(Broadcaster $broadcaster, bool $pulse): array
    {
        $this->prevPulses[$broadcaster->name] = $pulse;

        $allHigh = true;
        foreach ($this->prevPulses as $pulse) {
            if (!$pulse) {
                $allHigh = false;
                break;
            }
        }

        return $this->getOutgoingPulses(!$allHigh);
    }

    public function getState(): string
    {
        $state = '';
        foreach ($this->prevPulses as $pulse) {
            $state .= (int)$pulse;
        }
        return $state;
    }
}

class Output extends Broadcaster
{
    public function processPulse(Broadcaster $broadcaster, bool $pulse): array
    {
        //dump('OUT: ' . ($pulse ? 'high' : 'low'));
        return [];
    }
}

class LowPulseDetector extends Broadcaster
{
    public bool $lowPulseDetected = false;

    public function processPulse(Broadcaster $broadcaster, bool $pulse): array
    {
        if (!$pulse) {
            $this->lowPulseDetected = true;
        }
        return [];
    }
}