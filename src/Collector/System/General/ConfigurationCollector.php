<?php

namespace Startwind\Inventorio\Collector\System\General;

use Startwind\Inventorio\Collector\Collector;

class ConfigurationCollector implements Collector
{
    protected const COLLECTION_IDENTIFIER = 'SystemConfiguration';

    /**
     * @inheritDoc
     */
    public function getIdentifier(): string
    {
        return self::COLLECTION_IDENTIFIER;
    }

    /**
     * @inheritDoc
     */
    public function collect(): array
    {
        return [
            'cpu' => $this->getCpuCount(),
            'memory' => (int)($this->getMemorySize() / 1000),
            'disk' => $this->getDiskSize()
        ];
    }

    private function getCpuCount(): int
    {
        $os = PHP_OS_FAMILY;
        if ($os === 'Windows') {
            return (int)getenv("NUMBER_OF_PROCESSORS");
        } elseif ($os === 'Darwin' || $os === 'Linux') {
            return (int)shell_exec("getconf _NPROCESSORS_ONLN");
        }
        return 0;
    }

    private function getMemorySize(): float
    {
        $os = PHP_OS_FAMILY;
        if ($os === 'Windows') {
            $output = shell_exec("wmic computersystem get TotalPhysicalMemory");
            preg_match("/\d+/", $output, $matches);
            return isset($matches[0]) ? round((float)$matches[0] / 1024 / 1024, 2) : 0;
        } elseif ($os === 'Darwin') {
            $memBytes = trim(shell_exec("sysctl -n hw.memsize"));
            return round((float)$memBytes / 1024 / 1024, 2);
        } elseif ($os === 'Linux') {
            $memInfo = file_get_contents("/proc/meminfo");
            preg_match("/MemTotal:\s+(\d+)\skB/", $memInfo, $matches);
            return isset($matches[1]) ? round((float)$matches[1] / 1024, 2) :0;
        }
        return 0;
    }

    function getDiskSize(): float
    {
        $bytes = disk_total_space("/");
        return round($bytes / (1024 ** 3), 2);
    }

}
