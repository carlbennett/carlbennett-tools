<?php

namespace CarlBennett\Tools\Libraries\Core;

class VersionInfo
{
    // Set our own EOL constant, ignore/do not use PHP_EOL,
    // the file is generated externally by production deploy job.
    public const EOL = "\n";

    // Default path to version file relative to this file's directory.
    const VERSION_INFO_FILE = __DIR__ . '/../../etc/.rsync-version';

    private static ?array $version = null;

    /**
     * Block instantiation of this object. This class is completely static.
     */
    private function __construct()
    {
        throw new \LogicException('This static class cannot be constructed');
    }

    /**
     * Gets the version information array for use externally outside this class.
     *
     * @return array The version information.
     */
    public static function get(): array
    {
        if (!is_null(self::$version)) return self::$version;
        $r = [];

        $r['tools'] = self::deploymentVersion() ?? self::reflectVersion();
        $r['asset'] = self::assetVersion($r['tools']);
        $r['php'] = phpversion();

        ksort($r);
        self::$version = $r;
        return $r;
    }

    /**
     * Gets the asset tag string to append to asset URLs.
     * Useful for CDNs to key on when re-deploying cached assets.
     *
     * @param array $version The version array.
     * @return string The asset tag value.
     */
    private static function assetVersion(?array $version): string
    {
        return (
            !(\CarlBennett\Tools\Libraries\Core\Config::instance()->root['asset_versioning'] ?? false) ? '' :
            (!is_null($version) && isset($version[1]) ? $version[1] : date('YmdHis'))
        );
    }

    /**
     * Gets the version information array built by deployment automation:
     * 1. git tag/version identifier string
     * 2. git hash of last commit
     * 3. ISO8601 timestamp of last commit
     * 4. git hash and ISO8601 timestamp of the LICENSE.txt file
     *
     * @return array|null The information array, or null if not available.
     */
    private static function deploymentVersion(): ?array
    {
        if (!file_exists(self::VERSION_INFO_FILE))
        {
            return null;
        }

        $buffer = file_get_contents(self::VERSION_INFO_FILE);

        if (!is_string($buffer) || strlen($buffer) == 0)
        {
            return null;
        }

        return explode(self::EOL, $buffer);
    }

    /**
     * Gets the version information array by using git shell commands:
     * 1. git tag/version identifier string
     * 2. git hash of last commit
     * 3. ISO8601 timestamp of last commit
     * 4. git hash and ISO8601 timestamp of the LICENSE.txt file
     * Relative to current working directory which should be "src/"
     *
     * @return array|null The information array, or null if not available.
     */
    private static function reflectVersion(): ?array
    {
        $values = [null, null, null, null];

        $git_dir = '../.git';
        if (file_exists($git_dir) && is_dir($git_dir))
        {
            $identifier = shell_exec('git describe --always --tags');
            $hash = shell_exec('git rev-parse HEAD');
            $timestamp = shell_exec('git log -n 1 --pretty=\'%aI\' HEAD');
            $license = shell_exec('git log -n 1 --pretty=\'%h %aI\' ../LICENSE.txt');

            if (!empty($identifier)) $values[0] = trim($identifier);
            if (!empty($hash)) $values[1] = trim($hash);
            if (!empty($timestamp)) $values[2] = trim($timestamp);
            if (!empty($license)) $values[3] = trim($license);
        }

        if (is_null($values[0]) && is_null($values[1]) && is_null($values[2]) && is_null($values[3]))
        {
            $values = null;
        }

        return $values;
    }
}
