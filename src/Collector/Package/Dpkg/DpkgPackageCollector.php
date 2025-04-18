<?php

namespace Startwind\Inventorio\Collector\Package\Dpkg;

use Startwind\Inventorio\Collector\Collector;
use Startwind\Inventorio\Collector\OperatingSystem\OperatingSystemCollector;

/**
 * This collector returns details about all installed HomeBrew packages.
 */
class DpkgPackageCollector implements Collector
{
    protected const COLLECTION_IDENTIFIER = 'DpkgPackages';

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
        if (OperatingSystemCollector::getOsFamily() !== OperatingSystemCollector::OS_FAMILY_LINUX) {
            return [];
        }

        return [
            'packages' => $this->collectPackages(),
            'updatable' => $this->collectUpdatablePackages()
        ];
    }

    private function collectUpdatablePackages(): array
    {
        $output = shell_exec('apt list --upgradable 2>/dev/null');
        $lines = explode("\n", $output);
        array_shift($lines);

        $packages = [];

        foreach ($lines as $line) {
            if (trim($line) === '') continue;

            if (preg_match('/^([^\s\/]+)\/[^\s]+\s+([^\s]+).*upgradable from: ([^\]]+)/', $line, $matches)) {
                $packages[$matches[1]] = [
                    'currentVersion' => $matches[3],
                    'newVersion' => $matches[2]
                ];
            }
        }

        return $packages;
    }

    private function collectPackages(): array
    {
        $installed = shell_exec('command -v dpkg-query');

        if (!$installed) {
            return [];
        }

        $packages = shell_exec('dpkg-query -l');

        $packageLines = explode("\n", trim($packages));

        $installedPackages = [];

        foreach ($packageLines as $line) {
            if (str_starts_with($line, 'ii')) {
                $packageDetails = preg_split('/\s+/', $line);
                $packageName = $packageDetails[1];
                $packageVersion = $packageDetails[2];
                $installedPackages[$packageName] = [$packageVersion];
            }
        }

        return $installedPackages;
    }
}
