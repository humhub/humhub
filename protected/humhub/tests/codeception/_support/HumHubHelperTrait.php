<?php

/*
 * @link      https://www.humhub.org/
 * @copyright Copyright (c) 2023 HumHub GmbH & Co. KG
 * @license   https://www.humhub.com/licences
 */

namespace tests\codeception\_support;

use humhub\models\UrlOembed;
use humhub\modules\content\widgets\richtext\converter\RichTextToHtmlConverter;
use humhub\modules\content\widgets\richtext\converter\RichTextToMarkdownConverter;
use humhub\modules\content\widgets\richtext\converter\RichTextToPlainTextConverter;
use humhub\modules\content\widgets\richtext\converter\RichTextToShortTextConverter;
use PHPUnit\Framework\Constraint\LogicalNot;
use PHPUnit\Framework\Exception;
use Yii;
use yii\base\ErrorException;
use yii\base\InvalidConfigException;
use yii\helpers\FileHelper;
use yii\log\Dispatcher;

use function PHPUnit\Framework\assertEquals;

/**
 * Humhub Test Helper Functions
 *
 * @since 1.15
 */
trait HumHubHelperTrait
{
    protected static ?ArrayTarget $logTarget = null;
    protected static ?\yii\log\Logger $logOldLogger = null;
    private static $logOldDispatcher;

    protected function flushCache(?string $caller = null)
    {
        codecept_debug(sprintf('[%s] Flushing cache', $caller ?? __METHOD__));
        $cachePath = Yii::getAlias('@runtime/cache');
        if ($cachePath && is_dir($cachePath)) {
            FileHelper::removeDirectory($cachePath);
        }
        Yii::$app->cache->flush();
        Yii::$app->runtimeCache->flush();
        RichTextToShortTextConverter::flushCache();
        RichTextToHtmlConverter::flushCache();
        RichTextToPlainTextConverter::flushCache();
        RichTextToMarkdownConverter::flushCache();
        UrlOembed::flush();
    }

    protected function reloadSettings(?string $caller = null)
    {
        codecept_debug(sprintf('[%s] Reloading settings', $caller ?? __METHOD__));
        Yii::$app->settings->reload();

        foreach (Yii::$app->modules as $module) {
            if ($module instanceof \humhub\components\Module) {
                $module->settings->reload();
            }
        }
    }

    protected function deleteMails(?string $caller = null)
    {
        codecept_debug(sprintf('[%s] Deleting mails', $caller ?? __METHOD__));
        $path = Yii::getAlias('@runtime/mail');
        $files = glob($path . '/*'); // get all file names
        foreach ($files as $file) { // iterate files
            if (is_file($file)) {
                unlink($file); // delete file
            }
        }
    }

    /**
     * Start capturing log entries, in order to later check them with `assertLog*` and `assertNotLog*` functions
     *
     **********************************************************************************************************************
     *
     *  ## Assert Log Messages
     *
     *  In order to use the assertLog* functions, all you need to do is:
     *  - call `static::logInitialize();` before you expect the log output
     *  - run the code that should generate the log entries
     *  - call any of the `assertLog*` or `assertNotLog*` functions to test for the (un)expected log message
     *  - optionally call `static::logFlush();` if you want to run more log-generating code
     *  - optionally call `static::logReset();` when you no longer need the log to be captured within the test. Though, this
     *    will automatically be called during `tearDown()`.
     *
     *  If you need all log entries, call `static::logFilterMessageTexts()` with no arguments, or if you want the complete
     *  log information, use `static::logFilterMessages()`. For the format of the returned array elements to the latter,
     *  please see `\yii\log\Logger::$messages`.
     *
     *  For some kind of sample implementation, please see `\humhub\tests\codeception\unit\LogAssertionsSelfTest`
     * **********************************************************************************************************************
     *
     * @throws InvalidConfigException
     * @since 1.16
     * @see \yii\log\Logger::$messages
     * @see \humhub\tests\codeception\unit\LogAssertionsSelfTest
     */
    protected static function logInitialize()
    {
        if (self::$logTarget !== null) {
            return;
        }

        // save application's dispatcher
        self::$logOldDispatcher = Yii::$app->getLog();

        // create a new dispatcher target to store the log entries
        self::$logTarget = Yii::createObject([
            'class' => ArrayTarget::class,
            'levels' => [
                'error',
                'warning',
                'info',
                'trace', // aka debug
                // 'profile',
            ],
            'except' => [],
            'logVars' => [],
        ]);

        // create a new proxy logger that forwards log entries both to the current logger as well as to the above target
        Yii::setLogger(new Logger(['proxy' => Yii::getLogger()]));

        /**
         * will automagically connect to the logger set above
         *
         * @see Dispatcher::__construct
         * @see Dispatcher::getLogger
         */
        Yii::$app->set('log', Yii::createObject(['class' => Dispatcher::class, 'targets' => [static::class => self::$logTarget]]));
    }

    /**
     * Flush the captured log entries without stopping to capture them
     *
     * @since 1.16
     * @see static::logInitialize()
     */
    protected static function logFlush()
    {
        if (self::$logTarget === null) {
            return;
        }

        Yii::getLogger()->flush();
        self::$logTarget->messages = [];
        self::$logTarget->flush();
    }

    /**
     * Delete any captured log entry and stop capturing new entries. Automatically called by `static::tearDown()`
     *
     * @throws InvalidConfigException
     * @since 1.16
     * @see static::logInitialize()
     * @see static::tearDown()
     */
    protected static function logReset()
    {
        if (self::$logTarget === null) {
            return;
        }

        if (Yii::$app->getLog()->targets[static::class] ?? null === self::$logTarget) {
            Yii::$app->set('log', self::$logOldDispatcher);
        }
        self::$logOldDispatcher = null;

        $logger = Yii::getLogger();

        if ($logger instanceof Logger) {
            Yii::setLogger($logger->proxy);
        } else {
            Yii::setLogger(null);
        }

        self::$logTarget = null;
    }

    /**
     * Returns the array of captured log message arrays
     *
     * @param int|int[]|null $levels Array or bitmask of verbosity levels to be returned:
     *                                  Logger::LEVEL_ERROR, Logger::LEVEL_WARNING, Logger::LEVEL_INFO, Logger::LEVEL_TRACE
     * @param string[]|null $categories Array of categories to be returned or null for any
     * @param string[]|null $exceptCategories Array of categories NOT to be returned, or null for no exclusion
     *
     * @return array of message, following the format of `\yii\log\Logger::$messages`
     * @throws ErrorException
     * @since 1.16
     * @see static::logInitialize()
     * @see \yii\log\Logger::$messages
     */
    public static function &logFilterMessages($levels = null, ?array $categories = null, ?array $exceptCategories = null): array
    {
        if (self::$logTarget === null) {
            throw new ErrorException("Log has not been initialized");
        }

        Yii::getLogger()->flush();
        self::$logTarget->export();

        return self::$logTarget->getMessages($levels ?? 0, $categories, $exceptCategories);
    }

    /**
     * Returns an array of captured log messages as string (without the category or level)
     *
     * @throws ErrorException
     * @since 1.16
     * @see static::logInitialize()
     * @see static::logFilterMessages()
     */
    public static function logFilterMessageTexts($levels = null, ?array $categories = null, ?array $exceptCategories = null): array
    {
        return array_column(static::logFilterMessages($levels, $categories, $exceptCategories), 0);
    }

    /**
     * @param string|null $logMessage If not null, at least one of the filtered messages must match `$logMessage` exactly
     *
     * @throws ErrorException
     * @see static::logInitialize()
     * @see static::logFilterMessages()
     * @since 1.16
     */
    public static function assertLog(?string $logMessage = null, $levels = null, ?array $categories = null, ?array $exceptCategories = null, $errorMessage = null)
    {
        $messages = static::logFilterMessageTexts($levels, $categories, $exceptCategories);

        if ($logMessage === null) {
            static::assertNotEmpty($messages, $errorMessage ?? print_r(static::logFilterMessageTexts(), true));
        } else {
            static::assertContains($logMessage, $messages, $errorMessage ?? print_r(static::logFilterMessageTexts(), true));
        }
    }

    /**
     * @param string|null $logMessage If not null, at least one of the filtered messages must match `$logMessage` exactly
     *
     * @throws ErrorException
     * @see static::logInitialize()
     * @see static::logFilterMessages()
     * @since 1.16
     */
    public static function assertLogCount(int $expectedCount, ?string $logMessage = null, $levels = null, ?array $categories = null, ?array $exceptCategories = null, $errorMessage = null)
    {
        $messages = static::logFilterMessageTexts($levels, $categories, $exceptCategories);

        if ($logMessage !== null) {
            $messages = array_filter($messages, static fn($text) => $text === $logMessage);
        }

        static::assertCount($expectedCount, $messages, $errorMessage ?? print_r(static::logFilterMessageTexts(), true));
    }

    /**
     * @param string|null $logMessage If not null, at least one of the filtered messages must match `$logMessage` exactly
     *
     * @throws ErrorException
     * @since 1.16
     * @see static::logInitialize()
     * @see static::logFilterMessages()
     */
    public static function assertNotLog(?string $logMessage = null, $levels = null, ?array $categories = null, ?array $exceptCategories = null, $errorMessage = null)
    {
        $messages = static::logFilterMessageTexts($levels, $categories, $exceptCategories);

        if ($logMessage === null) {
            static::assertEmpty($messages, $errorMessage ?? print_r(static::logFilterMessageTexts(), true));
        } else {
            static::assertNotContains($logMessage, $messages, $errorMessage ?? print_r(static::logFilterMessageTexts(), true));
        }
    }

    /**
     * @param string $regex At least one of the filtered messages must match the given `$regex` pattern
     *
     * @throws ErrorException|Exception
     * @since 1.16
     * @see static::logInitialize()
     * @see static::logFilterMessages()
     */
    public static function assertLogRegex(string $regex, $levels = null, ?array $categories = null, ?array $exceptCategories = null, $errorMessage = null)
    {
        $messages = static::logFilterMessageTexts($levels, $categories, $exceptCategories);

        static::asserContainsRegex($regex, $messages, $errorMessage ?? print_r(static::logFilterMessageTexts(), true));
    }

    /**
     * @param string $regex At least one of the filtered messages must match the given `$regex` pattern
     *
     * @throws ErrorException|Exception
     * @since 1.16
     * @see static::logInitialize()
     * @see static::logFilterMessages()
     */
    public static function assertLogRegexCount(int $expectedCount, string $regex, $levels = null, ?array $categories = null, ?array $exceptCategories = null, $errorMessage = null)
    {
        $messages = static::logFilterMessageTexts($levels, $categories, $exceptCategories);

        if (count($messages)) {
            try {
                preg_match($regex, '');
            } catch (ErrorException $e) {
                throw new Exception("Invalid regex given: '{$regex}'");
            }

            $messages = array_filter($messages, static fn($text) => preg_match($regex, $text));
        }

        static::assertCount($expectedCount, $messages, $errorMessage ?? print_r(static::logFilterMessageTexts(), true));
    }

    /**
     * @param string $regex None of the filtered messages may match the given `$regex` pattern
     *
     * @throws ErrorException|Exception
     * @since 1.16
     * @see static::logInitialize()
     * @see static::logFilterMessages()
     */
    public static function assertNotLogRegex(string $regex, $levels = null, ?array $categories = null, ?array $exceptCategories = null, $errorMessage = null)
    {
        $messages = static::logFilterMessageTexts($levels, $categories, $exceptCategories);

        static::assertNotContainsRegex($regex, $messages, $errorMessage ?? print_r(static::logFilterMessageTexts(), true));
    }

    /**
     * Asserts that `$haystack` contains an element that matches the `$regex`
     *
     * @since 1.16
     * @throws Exception
     */
    public static function asserContainsRegex(string $regex, iterable $haystack, string $message = ''): void
    {
        $constraint = new TraversableContainsRegex($regex);

        static::assertThat($haystack, $constraint, $message);
    }

    /**
     * Asserts that `$haystack` does not contain an element that matches the `$regex`
     *
     * @since 1.16
     * @throws Exception
     */
    public static function assertNotContainsRegex(string $regex, iterable $haystack, string $message = ''): void
    {
        $constraint = new LogicalNot(
            new TraversableContainsRegex($regex),
        );

        static::assertThat($haystack, $constraint, $message);
    }
}
