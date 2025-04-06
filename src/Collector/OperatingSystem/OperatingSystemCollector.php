<?php

namespace Startwind\Inventorio\Collector\OperatingSystem;

use Startwind\Inventorio\Collector\Collector;

/**
 * This collector returns details about the operating system.
 *
 * - family: the OS family (MacOs, Linux, Windows). Please use the provided constants.
 * - version: the OS version
 *
 */
class OperatingSystemCollector implements Collector
{
    public const string OS_FAMILY_MAC = 'macos';
    public const string OS_FAMILY_LINUX = 'linux';
    public const string OS_FAMILY_WINDOWS = 'windows';
    public const string OS_FAMILY_UNKNOWN = 'unknown';
    public const string OS_VERSION_UNKNOWN = 'unknown';

    protected const string COLLECTION_IDENTIFIER = 'OperatingSystem';

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
        $osFamily = self::getOsFamily();

        $data = [
            'family' => $osFamily,
            'version' => $this->getOsVersion($osFamily)
        ];

        if ($osFamily == self::OS_FAMILY_LINUX) {
            $data['distribution'] = $this->getOsDistribution();
        }

        return $data;
    }

    /**
     * Return the version of the operating system
     */
    private function getOsVersion(string $family): string
    {
        switch ($family) {
            case self::OS_FAMILY_MAC:
                return trim(shell_exec('sw_vers -productVersion'));
            case self::OS_FAMILY_LINUX:
                $osInfo = $this->getLinuxOsInfo();
                return $osInfo['VERSION_ID'];
            default:
                return self::OS_VERSION_UNKNOWN;
        }
    }

    /**
     * Return the distribution of the linux operating system
     */
    private function getOsDistribution(): string
    {
        $osInfo = $this->getLinuxOsInfo();
        return $osInfo['NAME'];
    }

    public function getLinuxOsInfo(): array
    {
        $osRelease = file_get_contents('/etc/os-release');
        $lines = explode("\n", $osRelease);
        $osInfo = array();
        foreach ($lines as $line) {
            if (str_contains($line, "=")) {
                list($key, $value) = explode("=", $line, 2);
                $value = trim($value, '"');
                $osInfo[$key] = $value;
            }
        }
        return $osInfo;
    }

    /**
     * Return the operating system family (supported: MacOs, Windows, Linux)
     */
    public static function getOsFamily(): string
    {
        switch (strtolower(PHP_OS_FAMILY)) {
            case 'darwin':
                return self::OS_FAMILY_MAC;
            case 'linux';
                return self::OS_FAMILY_LINUX;
            case 'windows':
                return self::OS_FAMILY_WINDOWS;
            default:
                return self::OS_FAMILY_UNKNOWN;
        }
    }
}
