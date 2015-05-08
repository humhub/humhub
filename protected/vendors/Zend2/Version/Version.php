<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Version;

use Zend\Json\Json;

/**
 * Class to store and retrieve the version of Zend Framework.
 */
final class Version
{
    /**
     * Zend Framework version identification - see compareVersion()
     */
    const VERSION = '2.2.5';

    /**
     * Github Service Identifier for version information is retreived from
     */
    const VERSION_SERVICE_GITHUB = 'GITHUB';

    /**
     * Zend (framework.zend.com) Service Identifier for version information is retreived from
     */
    const VERSION_SERVICE_ZEND = 'ZEND';

    /**
     * The latest stable version Zend Framework available
     *
     * @var string
     */
    protected static $latestVersion;

    /**
     * Compare the specified Zend Framework version string $version
     * with the current Zend\Version\Version::VERSION of Zend Framework.
     *
     * @param  string  $version  A version string (e.g. "0.7.1").
     * @return int           -1 if the $version is older,
     *                           0 if they are the same,
     *                           and +1 if $version is newer.
     *
     */
    public static function compareVersion($version)
    {
        $version = strtolower($version);
        $version = preg_replace('/(\d)pr(\d?)/', '$1a$2', $version);
        return version_compare($version, strtolower(self::VERSION));
    }

    /**
     * Fetches the version of the latest stable release.
     *
     * By default, this uses the API provided by framework.zend.com for version
     * retrieval.
     *
     * If $service is set to VERSION_SERVICE_GITHUB, this will use the GitHub
     * API (v3) and only returns refs that begin with * 'tags/release-'.
     * Because GitHub returns the refs in alphabetical order, we need to reduce
     * the array to a single value, comparing the version numbers with
     * version_compare().
     *
     * @see http://developer.github.com/v3/git/refs/#get-all-references
     * @link https://api.github.com/repos/zendframework/zf2/git/refs/tags/release-
     * @link http://framework.zend.com/api/zf-version?v=2
     * @param string $service Version Service with which to retrieve the version
     * @return string
     */
    public static function getLatest($service = self::VERSION_SERVICE_ZEND)
    {
        if (null === static::$latestVersion) {
            static::$latestVersion = 'not available';
            if ($service == self::VERSION_SERVICE_GITHUB) {
                $url  = 'https://api.github.com/repos/zendframework/zf2/git/refs/tags/release-';

                $apiResponse = Json::decode(file_get_contents($url), Json::TYPE_ARRAY);

                // Simplify the API response into a simple array of version numbers
                $tags = array_map(function ($tag) {
                    return substr($tag['ref'], 18); // Reliable because we're filtering on 'refs/tags/release-'
                }, $apiResponse);

                // Fetch the latest version number from the array
                static::$latestVersion = array_reduce($tags, function ($a, $b) {
                    return version_compare($a, $b, '>') ? $a : $b;
                });
            } elseif ($service == self::VERSION_SERVICE_ZEND) {
                $handle = fopen('http://framework.zend.com/api/zf-version?v=2', 'r');
                if (false !== $handle) {
                    static::$latestVersion = stream_get_contents($handle);
                    fclose($handle);
                }
            }
        }

        return static::$latestVersion;
    }

    /**
     * Returns true if the running version of Zend Framework is
     * the latest (or newer??) than the latest tag on GitHub,
     * which is returned by static::getLatest().
     *
     * @return bool
     */
    public static function isLatest()
    {
        return static::compareVersion(static::getLatest()) < 1;
    }
}
