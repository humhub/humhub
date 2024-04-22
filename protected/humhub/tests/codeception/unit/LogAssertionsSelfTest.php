<?php

/*
 * @link      https://www.humhub.org/
 * @copyright Copyright (c) 2023 HumHub GmbH & Co. KG
 * @license   https://www.humhub.com/licences
 */

/**
 * @noinspection PhpClassConstantAccessedViaChildClassInspection
 */

namespace humhub\tests\codeception\unit;

use Codeception\Lib\Console\Output;
use Codeception\Util\Debug;
use PHPUnit\Framework\Exception;
use tests\codeception\_support\HumHubDbTestCase;
use tests\codeception\_support\Logger;
use Yii;

/**
 * Self-test of the Log Assertions available to other tests
 *
 * While this could be used as an example for the assertLog* functions, please note that none of the local methods are
 * required for anything else than for this self-test.
 *
 * @since 1.16
 * @see static::logInitialize()
 */
class LogAssertionsSelfTest extends HumHubDbTestCase
{
    protected static ?Output $originalOutput = null;
    protected static Output $output;

    /**
     * @var resource|bool
     */
    protected static $streamFilter;

    public static ?string $data = null;


    public static function setUpBeforeClass(...$args): void
    {
        self::$output = new class ($config = ['interactive' => false, 'colors' => false]) extends Output {
            public bool $forward = false;

            public function doWrite(string $message, bool $newline)
            {
                if ($this->forward) {
                    parent::doWrite($message, $newline);
                }

                if ($newline) {
                    $message .= \PHP_EOL;
                }

                LogAssertionsSelfTest::addData($message);
            }
        };

        self::$output->waitForDebugOutput = false;

        if (Debug::isEnabled()) {
            $rcDebug = new \ReflectionClass(Debug::class);
            self::$originalOutput = $rcDebug->getStaticPropertyValue('output');
            self::$output->forward = true;
        } else {
            self::$originalOutput = new class ($config = ['interactive' => false, 'colors' => false]) extends Output {
                public function debug($message)
                {
                    // don't do anything
                }
            };
        }

        parent::setUpBeforeClass();
    }

    protected function setUp(): void
    {
        parent::setUp();

        Debug::setOutput(self::$output);

        static::clean();
    }

    protected function tearDown(): void
    {
        // reset output to original
        Debug::setOutput(self::$originalOutput);

        static::clean();

        parent::tearDown();
    }

    public function testNormalDebugMessage()
    {
        Yii::error("Foo", "error");
        self::assertEquals("  [error] 'Foo'\n", static::getClean());

        Yii::warning("Foo", "warning");
        self::assertEquals("  [warning] 'Foo'\n", static::getClean());

        Yii::info("Foo", "info");
        self::assertEquals("  [info] 'Foo'\n", static::getClean());

        /**
         * debug messages are not prited
         * @see \Codeception\Lib\Connector\Yii2\Logger::log()
         */
        Yii::debug("Foo", "debug");
        self::assertEquals(null, static::getClean());
    }

    public function testInitializationRequired()
    {
        $this->expectException(yii\base\ErrorException::class);
        $this->expectExceptionMessage('Log has not been initialized');

        static::assertLogCount(1);
    }

    /**
     * @noinspection UnsetConstructsCanBeMergedInspection
     */
    public function testAssertingDebugMessage()
    {
        static::logInitialize();

        static::assertLogCount(0);

        Yii::error("Foo", "LogAssertionsSelfTest_error");
        self::assertEquals("  [LogAssertionsSelfTest_error] 'Foo'\n", static::getClean());
        static::assertLogCount(1);
        static::assertLogCount(1, null, Logger::LEVEL_ERROR);
        static::assertLogCount(0, null, Logger::LEVEL_WARNING | Logger::LEVEL_INFO | Logger::LEVEL_TRACE);
        static::assertLogCount(1, 'Foo');
        static::assertLog('Foo');
        static::assertLog('Foo', Logger::LEVEL_ERROR);
        static::assertLog('Foo', Logger::LEVEL_ERROR, ['LogAssertionsSelfTest_error']);
        static::assertLog('Foo', null, ['LogAssertionsSelfTest_error']);
        static::assertLog(null, Logger::LEVEL_ERROR);
        static::assertLog(null, Logger::LEVEL_ERROR, ['LogAssertionsSelfTest_error']);
        static::assertLog(null, null, ['LogAssertionsSelfTest_error']);
        static::assertLog(null, null, null, ['LogAssertionsSelfTest_warning']);
        static::assertLog();
        static::assertNotLog('Bar');
        static::assertNotLog(null, Logger::LEVEL_WARNING);
        static::assertNotLog(null, null, ['LogAssertionsSelfTest_warning']);
        static::assertNotLog('Bar', null, ['LogAssertionsSelfTest_error', 'LogAssertionsSelfTest_warning']);
        static::assertNotLog('Foo', null, null, ['LogAssertionsSelfTest_error']);

        Yii::warning("Foo", "LogAssertionsSelfTest_warning");
        self::assertEquals("  [LogAssertionsSelfTest_warning] 'Foo'\n", static::getClean());
        static::assertLogCount(2);
        static::assertLogCount(1, null, Logger::LEVEL_ERROR);
        static::assertLogCount(1, null, Logger::LEVEL_WARNING);
        static::assertLogCount(2, null, Logger::LEVEL_ERROR | Logger::LEVEL_WARNING);
        static::assertLogCount(0, null, Logger::LEVEL_INFO | Logger::LEVEL_TRACE);
        static::assertLogCount(2, 'Foo');
        static::assertLog('Foo', Logger::LEVEL_ERROR, ['LogAssertionsSelfTest_error']);
        static::assertLog('Foo', Logger::LEVEL_WARNING, ['LogAssertionsSelfTest_warning']);
        static::assertNotLog('Bar');

        Yii::info("Bar", "LogAssertionsSelfTest");
        self::assertEquals("  [LogAssertionsSelfTest] 'Bar'\n", static::getClean());
        static::assertLogCount(3);
        static::assertLogCount(1, null, Logger::LEVEL_ERROR);
        static::assertLogCount(1, null, Logger::LEVEL_WARNING);
        static::assertLogCount(1, null, Logger::LEVEL_INFO);
        static::assertLogCount(2, null, Logger::LEVEL_ERROR | Logger::LEVEL_WARNING);
        static::assertLogCount(2, null, Logger::LEVEL_ERROR | Logger::LEVEL_INFO);
        static::assertLogCount(2, null, Logger::LEVEL_WARNING | Logger::LEVEL_INFO);
        static::assertLogCount(1, null, Logger::LEVEL_INFO | Logger::LEVEL_TRACE);
        static::assertLogCount(0, null, Logger::LEVEL_TRACE);
        static::assertLogCount(2, 'Foo');
        static::assertLogCount(1, 'Bar');
        static::assertLog('Bar', Logger::LEVEL_INFO, ['LogAssertionsSelfTest']);

        /**
         * debug messages are not printed
         * @see \Codeception\Lib\Connector\Yii2\Logger::log()
         */
        Yii::debug("Foo", "LogAssertionsSelfTest");
        self::assertEquals(null, static::getClean());
        static::assertLogCount(4);
        static::assertLogCount(1, null, Logger::LEVEL_ERROR);
        static::assertLogCount(1, null, Logger::LEVEL_WARNING);
        static::assertLogCount(1, null, Logger::LEVEL_INFO);
        static::assertLogCount(1, null, Logger::LEVEL_TRACE);
        static::assertLogCount(2, null, Logger::LEVEL_ERROR | Logger::LEVEL_WARNING);
        static::assertLogCount(2, null, Logger::LEVEL_ERROR | Logger::LEVEL_INFO);
        static::assertLogCount(2, null, Logger::LEVEL_WARNING | Logger::LEVEL_INFO);
        static::assertLogCount(2, null, Logger::LEVEL_INFO | Logger::LEVEL_TRACE);
        static::assertLogCount(3, 'Foo');
        static::assertLogCount(1, 'Bar');
        static::assertLogCount(2, null, null, ['LogAssertionsSelfTest']);
        static::assertLog('Bar', Logger::LEVEL_INFO, ['LogAssertionsSelfTest']);
        static::assertLog('Foo', Logger::LEVEL_TRACE, ['LogAssertionsSelfTest']);
        static::assertLog('Bar', null, ['LogAssertionsSelfTest']);
        static::assertLog('Foo', null, ['LogAssertionsSelfTest']);

        static::assertNotLog('Fo');
        static::assertNotLog('FooBar');

        static::assertLogRegex('@oo@');
        static::assertNotLogRegex('@Oo@');
        static::assertLogRegex('@Oo@i');
        static::assertLogRegexCount(1, '@foo|Ba@');

        static::assertEquals(
            [
                'Foo',
                'Foo',
                'Bar',
                'Foo',
            ],
            static::logFilterMessageTexts(),
        );

        $messages = static::logFilterMessages();

        /**
         * Cleanup
         *
         * @see \yii\log\Logger::$messages
         * */
        foreach ($messages as &$message) {
            // unset timestamp
            unset($message[3]);

            // traces
            //unset($message[4]);

            // memory usage
            unset($message[5]);
        }

        static::assertEquals(
            [
                0 => [
                    0 => 'Foo',
                    1 => 1,
                    2 => 'LogAssertionsSelfTest_error',
                    4 => [],
                ],
                1 =>  [
                    0 => 'Foo',
                    1 => 2,
                    2 => 'LogAssertionsSelfTest_warning',
                    4 =>  [],
                ],
                2 =>  [
                    0 => 'Bar',
                    1 => 4,
                    2 => 'LogAssertionsSelfTest',
                    4 =>  [],
                ],
                3 =>  [
                    0 => 'Foo',
                    1 => 8,
                    2 => 'LogAssertionsSelfTest',
                    4 =>  [],
                ],
            ],
            $messages,
        );

        static::logFlush();
        static::assertLogCount(0);

        /**
         * Note: this is also automatically done in @see HumHubDbTestCase::tearDown()
         * As such, it is mainly useful to stop capturing within a test.
         */
        static::logReset();
    }

    /**
     * @noinspection UnsetConstructsCanBeMergedInspection
     */
    public function testInvalidRegex1()
    {
        static::logInitialize();

        Yii::debug("Foo", "LogAssertionsSelfTest");
        self::assertEquals(null, static::getClean());
        static::assertLogCount(1);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage("Invalid regex given: '@Oo'");

        static::assertLogRegex('@Oo');
    }

    /**
     * @noinspection UnsetConstructsCanBeMergedInspection
     */
    public function testInvalidRegex2()
    {
        static::logInitialize();

        Yii::debug("Foo", "LogAssertionsSelfTest");
        self::assertEquals(null, static::getClean());
        static::assertLogCount(1);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage("Invalid regex given: '@Oo'");

        static::assertLogRegexCount(1, '@Oo');
    }

    public static function get(): ?string
    {
        return self::$data;
    }

    public static function getClean(): ?string
    {
        $data = self::$data;

        self::clean();

        return $data;
    }

    public static function clean(): void
    {
        self::$data = null;
    }

    public static function addData(string $data): void
    {
        static::$data .= $data;
    }
}
