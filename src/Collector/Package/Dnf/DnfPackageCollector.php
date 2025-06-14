<?php

namespace Startwind\Inventorio\Collector\Package\Dnf;

use Startwind\Inventorio\Collector\Collector;
use Startwind\Inventorio\Collector\OperatingSystem\OperatingSystemCollector;
use Startwind\Inventorio\Exec\Runner;

/**
 * This collector returns details about all installed DNF (RPM-based) packages.
 */
class DnfPackageCollector implements Collector
{
    protected const COLLECTION_IDENTIFIER = 'DnfPackages';

    public function getIdentifier(): string
    {
        return self::COLLECTION_IDENTIFIER;
    }

    public function collect(): array
    {
        if (OperatingSystemCollector::getOsFamily() !== OperatingSystemCollector::OS_FAMILY_LINUX) {
            return [];
        }

        $result = [
            'packages' => $this->collectPackages(),
            'updatable' => $this->collectUpdatablePackages()
        ];

        return $result;
    }

    private function collectPackages(): array
    {
        if (!Runner::getInstance()->commandExists('rpm')) {
            return [];
        }

        $command = 'rpm -qa --qf "%{NAME} %{VERSION}-%{RELEASE}\n"';

        $output = Runner::getInstance()->run($command)->getOutput();

        $packages = (explode("\n", $output));

        $packageList = [];

        foreach ($packages as $packageObject) {
            $parts = explode(' ', $packageObject);
            if (count($parts) > 0 && $parts[0]) {
                $packageList[$parts[0]] = $parts[1];
            }
        }

        return $packageList;
    }

    private function collectUpdatablePackages(): array
    {
        if (!Runner::getInstance()->commandExists('dnf')) {
            return [];
        }

        $output = Runner::getInstance()->run('dnf check-update -q || true')->getOutput();
        $lines = explode("\n", $output);

        $packages = [];

        foreach ($lines as $line) {
            if (preg_match('/^(\S+)\s+(\S+)\s+(\S+)/', $line, $matches)) {
                $packages[$matches[1]] = [
                    'currentVersion' => null, // Fedora does not show current version here
                    'newVersion' => $matches[2]
                ];
            }
        }

        return $packages;
    }
}