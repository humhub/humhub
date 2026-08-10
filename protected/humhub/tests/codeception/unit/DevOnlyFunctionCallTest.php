<?php

/*
 * @link      https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license   https://www.humhub.com/licences
 */

namespace humhub\tests\codeception\unit;

use Codeception\Test\Unit;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RegexIterator;

/**
 * Guards against calls to dev-only functions (e.g. from the Codeception package)
 * leaking into production code, where they are not autoloaded and cause fatal errors.
 */
class DevOnlyFunctionCallTest extends Unit
{
    public function testNoCodeceptDebugInProductionCode()
    {
        $root = dirname(__DIR__, 3);

        $iterator = new RegexIterator(
            new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root)),
            '/\.php$/',
        );

        $hits = [];
        foreach ($iterator as $file) {
            $path = $file->getPathname();
            if (str_contains((string) $path, DIRECTORY_SEPARATOR . 'tests' . DIRECTORY_SEPARATOR)) {
                continue;
            }
            if (str_contains(file_get_contents($path), 'codecept_debug(')) {
                $hits[] = substr((string) $path, strlen($root) + 1);
            }
        }

        $this->assertEmpty($hits, 'codecept_debug() must not be called in production code: ' . implode(', ', $hits));
    }
}
