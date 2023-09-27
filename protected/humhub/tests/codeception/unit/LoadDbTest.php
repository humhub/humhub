<?php

/*
 * @link      https://www.humhub.org/
 * @copyright Copyright (c) 2023 HumHub GmbH & Co. KG
 * @license   https://www.humhub.com/licences
 */

/**
 * @noinspection PhpIllegalPsrClassPathInspection
 */

namespace humhub\tests\codeception\unit;

use Codeception\Exception\InjectionException;
use Codeception\Exception\ModuleException;
use Codeception\Lib\Console\Output;
use Codeception\Lib\ModuleContainer;
use Codeception\Module\Yii2;
use Codeception\Test\Metadata;
use humhub\modules\queue\models\QueueExclusive;
use tests\codeception\_support\HumHubDbTestCase;

/**
 * Test Debugging DbLoad Helper
 *
 * Use this test to fill up the database with fixture test data to manually debug
 *
 * Example usage:
 * ```
 * HUMHUB_DB_INITIALIZE= php $HUMHUB_VENDOR_BIN/codecept run unit LoadDbTest
 * ```
 *
 * @since 1.15
 */
class LoadDbTest extends HumHubDbTestCase
{
    protected bool $humhubLoadDb = false;
    protected static Output $humhubConsole;

    public function _fixtures(...$args): array
    {
        if (false !== getenv('HUMHUB_DB_INITIALIZE') || in_array('--load', $_SERVER['argv'], true)) {
            $this->humhubLoadDb = true;
            $this->fixtureConfig = ['default'];

            foreach (['queue', QueueExclusive::tableName()] as $table) {
                self::dbDelete($table);
            }

            self::$humhubConsole->writeln(sprintf(' - Going to load fixtures: %s ...', implode(', ', $this->fixtureConfig)));
        }

        return parent::_fixtures();
    }


    public static function setUpBeforeClass(...$args): void
    {
        self::$humhubConsole = new Output([]);

        parent::setUpBeforeClass();
    }

    /**
     * @throws InjectionException
     * @throws ModuleException
     */
    public function testLoadFixtures()
    {
        /**
         * @var Yii2 $yii2
         * @var Metadata $metadata
         * @var ModuleContainer $service
         */
        $metadata = $this->getMetadata();
        $service = $metadata->getService('modules');
        $yii2 = $service->getModule('Yii2');

        if ($this->humhubLoadDb) {
            static::assertNotEmpty($yii2->loadedFixtures);

            // prevent fixtures from being unloaded
            $yii2->loadedFixtures = [];
        } else {
            static::assertEmpty($yii2->loadedFixtures);
        }
    }
}
