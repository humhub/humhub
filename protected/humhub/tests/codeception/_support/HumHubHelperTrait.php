<?php

/*
 * @link      https://www.humhub.org/
 * @copyright Copyright (c) 2023 HumHub GmbH & Co. KG
 * @license   https://www.humhub.com/licences
 */

/**
 * @noinspection PhpIllegalPsrClassPathInspection
 */

namespace tests\codeception\_support;

use Codeception\Exception\ModuleException;
use Codeception\Module\Yii2;
use humhub\components\behaviors\PolymorphicRelation;
use humhub\libs\UUID;
use humhub\models\UrlOembed;
use humhub\modules\activity\models\Activity;
use humhub\modules\content\widgets\richtext\converter\RichTextToHtmlConverter;
use humhub\modules\content\widgets\richtext\converter\RichTextToMarkdownConverter;
use humhub\modules\content\widgets\richtext\converter\RichTextToPlainTextConverter;
use humhub\modules\content\widgets\richtext\converter\RichTextToShortTextConverter;
use humhub\modules\notification\models\Notification;
use PHPUnit\Framework\Constraint\LogicalNot;
use PHPUnit\Framework\Exception;
use TypeError;
use Yii;
use yii\base\ErrorException;
use yii\base\Event;
use yii\base\InvalidConfigException;
use yii\db\ActiveRecord;
use yii\db\Command;
use yii\db\ExpressionInterface;
use yii\db\Query;
use yii\helpers\ArrayHelper;
use yii\helpers\FileHelper;
use yii\log\Dispatcher;

/**
 * Humhub Test Helper Functions
 *
 * @since 1.15
 */
trait HumHubHelperTrait
{
    public array $firedEvents = [];
    protected static ?ArrayTarget $logTarget = null;
    private static $logOldDispatcher;

    protected function tearDown(): void
    {
        static::logReset();

        parent::tearDown();
    }

    protected static function flushCache(?string $caller = null)
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

    protected static function reloadSettings(?string $caller = null)
    {
        codecept_debug(sprintf('[%s] Reloading settings', $caller ?? __METHOD__));
        Yii::$app->settings->reload();

        foreach (Yii::$app->modules as $module) {
            if ($module instanceof \humhub\components\Module) {
                $module->settings->reload();
            }
        }
    }

    protected static function deleteMails(?string $caller = null)
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
     * @return Yii2
     * @throws ModuleException
     */
    public function getYiiModule(): Yii2
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->getModule('Yii2');
    }

    /**
     * GENERAL
     * =======
     */

    /**
     * Asserts that `$haystack` contains an element that matches the `$regex`
     *
     * @throws Exception
     * @since 1.15
     */
    public static function assertContainsRegex(string $regex, iterable $haystack, string $message = ''): void
    {
        $constraint = new TraversableContainsRegex($regex);

        static::assertThat($haystack, $constraint, $message);
    }

    /**
     * Asserts that `$haystack` does not contain an element that matches the `$regex`
     *
     * @throws Exception
     * @since 1.15
     */
    public static function assertNotContainsRegex(string $regex, iterable $haystack, string $message = ''): void
    {
        $constraint = new LogicalNot(
            new TraversableContainsRegex($regex),
        );

        static::assertThat($haystack, $constraint, $message);
    }

    /**
     * ACTIVITIES
     * ==========
     */

    public static function assertHasActivity($class, ActiveRecord $source, $msg = '')
    {
        $activity = Activity::findOne([
            'class' => $class,
            'object_model' => PolymorphicRelation::getObjectModel($source),
            'object_id' => $source->getPrimaryKey(),
        ]);

        static::assertNotNull($activity, $msg);
    }

    /**
     * EVENTS (Yii)
     * ============
     */

    /**
     * Used to check the fired events. Example:
     * ```
     * // register the events you are interested in
     * \yii\base\Event::on(EventA::class, EventA::EVENT_NAME_1, [$this, 'handleEvent'], $optionalEventData);
     * $instance->on(EventB::class, EventB::EVENT_NAME_2, [$this, 'handleEvent'], $optionalEventData);
     *
     * // Then do the stuff that would raise the event
     *
     * // Then assert the events - adapt to what you expect. It is not a requirement that both events you've registered
     * // to are actually fired. In fact, you might want to register to one event above, and then make sure it was *not*
     * // fired by not adding it in the list below.
     * $this->assertEvents([
     *      [
     *          'class' => EventA::class,
     *          'event' => EventA::EVENT_NAME_1,
     *          'sender' => $instance,                      // adapt to what you expect
     *          'data' => null,                             // or $optionalEventData
     *          'handled' => false,                         // adapt to what you expect
     *          'extra' => [                                // example according to the example in static::handleEvent()
     *              EventA::EVENT_NAME_1 => Module1::class,
     *          ],
     *      ],
     *      [
     *          'class' => EventB::class,
     *          'event' => EventB::EVENT_NAME_2',
     *          'sender' => $instance,
     *          'data' => null,                             // or $optionalEventData
     *          'handled' => false,                         // adapt to what you expect
     *          'extra' => [                                // example according to the example in static::handleEvent()
     *              $id1 => $name1,
     *              $id2 => $name2,
     *          ],
     *      ],
     * ]);
     * ```
     *
     * @since 1.15
     * @see static::handleEvent()
     */
    public function assertEvents(array $events = [], string $message = ''): void
    {
        static::assertEquals($events, $this->firedEvents, $message);

        $this->firedEvents = [];
    }

    /**
     * LOGGING
     * =======
     */

    /**
     * @param string|null $logMessage If not null, at least one of the filtered messages must match `$logMessage`
     *     exactly
     *
     * @throws ErrorException
     * @see static::logInitialize()
     * @see static::logFilterMessages()
     * @since 1.15
     */
    public static function assertLog(?string $logMessage = null, $levels = null, ?array $categories = null, ?array $exceptCategories = null, $errorMessage = null)
    {
        $messages = static::logFilterMessageTexts($levels, $categories, $exceptCategories);

        if ($logMessage === null) {
            static::assertNotEmpty($messages, $errorMessage ?? print_r(static::logFilterMessageTexts(), true));
        } else {
            static::assertContains(
                $logMessage,
                $messages,
                $errorMessage ?? print_r(static::logFilterMessageTexts(), true),
            );
        }
    }

    /**
     * @param string|null $logMessage If not null, at least one of the filtered messages must match `$logMessage`
     *     exactly
     *
     * @throws ErrorException
     * @see static::logInitialize()
     * @see static::logFilterMessages()
     * @since 1.15
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
     * @param string|null $logMessage If not null, at least one of the filtered messages must match `$logMessage`
     *     exactly
     *
     * @throws ErrorException
     * @since 1.15
     * @see static::logInitialize()
     * @see static::logFilterMessages()
     */
    public static function assertNotLog(?string $logMessage = null, $levels = null, ?array $categories = null, ?array $exceptCategories = null, $errorMessage = null)
    {
        $messages = static::logFilterMessageTexts($levels, $categories, $exceptCategories);

        if ($logMessage === null) {
            static::assertEmpty($messages, $errorMessage ?? print_r(static::logFilterMessageTexts(), true));
        } else {
            static::assertNotContains(
                $logMessage,
                $messages,
                $errorMessage ?? print_r(static::logFilterMessageTexts(), true),
            );
        }
    }

    /**
     * @param string $regex At least one of the filtered messages must match the given `$regex` pattern
     *
     * @throws ErrorException|Exception
     * @since 1.15
     * @see static::logInitialize()
     * @see static::logFilterMessages()
     */
    public static function assertLogRegex(string $regex, $levels = null, ?array $categories = null, ?array $exceptCategories = null, $errorMessage = null)
    {
        $messages = static::logFilterMessageTexts($levels, $categories, $exceptCategories);

        static::assertContainsRegex($regex, $messages, $errorMessage ?? print_r(static::logFilterMessageTexts(), true));
    }

    /**
     * @param string $regex At least one of the filtered messages must match the given `$regex` pattern
     *
     * @throws ErrorException|Exception
     * @since 1.15
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
     * @since 1.15
     * @see static::logInitialize()
     * @see static::logFilterMessages()
     */
    public static function assertNotLogRegex(string $regex, $levels = null, ?array $categories = null, ?array $exceptCategories = null, $errorMessage = null)
    {
        $messages = static::logFilterMessageTexts($levels, $categories, $exceptCategories);

        static::assertNotContainsRegex(
            $regex,
            $messages,
            $errorMessage ?? print_r(static::logFilterMessageTexts(), true),
        );
    }

    /**
     * MAILS
     * =====
     */

    /**
     * @see assertSentEmail
     * @since 1.3
     */
    public function assertMailSent($count = 0)
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $this->getYiiModule()->seeEmailIsSent($count);
    }

    /**
     * @param int $count
     *
     * @throws ModuleException
     * @since 1.3
     */
    public function assertSentEmail(int $count = 0)
    {
        $this->getYiiModule()->seeEmailIsSent($count);
    }

    public function assertEqualsLastEmailTo($to, $strict = true)
    {
        if (is_string($to)) {
            $to = [$to];
        }

        /** @noinspection PhpUnhandledExceptionInspection */
        $message = $this->getYiiModule()->grabLastSentEmail();
        $expected = $message->getTo();

        foreach ($to as $email) {
            $this->assertArrayHasKey($email, $expected);
        }

        if ($strict) {
            $this->assertCount(count($expected), $to);
        }
    }

    public function assertEqualsLastEmailSubject($subject)
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $message = $this->getYiiModule()->grabLastSentEmail();
        $this->assertEquals($subject, str_replace(["\n", "\r"], '', $message->getSubject()));
    }

    /**
     * NOTIFICATIONS
     * =============
     */

    public static function assertHasNotification($class, ActiveRecord $source, $originator_id = null, $target_id = null, $msg = '')
    {
        $notificationQuery = Notification::find()->where([
            'class' => $class,
            'source_class' => PolymorphicRelation::getObjectModel($source),
            'source_pk' => $source->getPrimaryKey(),
        ]);

        if (is_string($target_id)) {
            $msg = $target_id;
            $target_id = null;
        }

        if ($originator_id != null) {
            $notificationQuery->andWhere(['originator_user_id' => $originator_id]);
        }

        if ($target_id != null) {
            $notificationQuery->andWhere(['user_id' => $target_id]);
        }

        static::assertNotEmpty($notificationQuery->all(), $msg);
    }

    public static function assertHasNoNotification($class, ActiveRecord $source, $originator_id = null, $target_id = null, $msg = '')
    {
        $notificationQuery = Notification::find()->where([
            'class' => $class,
            'source_class' => PolymorphicRelation::getObjectModel($source),
            'source_pk' => $source->getPrimaryKey(),
        ]);

        if ($originator_id != null) {
            $notificationQuery->andWhere(['originator_user_id' => $originator_id]);
        }

        if ($target_id != null) {
            $notificationQuery->andWhere(['user_id' => $target_id]);
        }

        static::assertEmpty($notificationQuery->all(), $msg);
    }

    public static function assertEqualsNotificationCount($count, $class, ActiveRecord $source, $originator_id = null, $target_id = null, $msg = '')
    {
        $notificationQuery = Notification::find()->where([
            'class' => $class,
            'source_class' => PolymorphicRelation::getObjectModel($source),
            'source_pk' => $source->getPrimaryKey(),
        ]);

        if ($originator_id != null) {
            $notificationQuery->andWhere(['originator_user_id' => $originator_id]);
        }

        if ($target_id != null) {
            $notificationQuery->andWhere(['user_id' => $target_id]);
        }

        static::assertEquals($count, $notificationQuery->count(), $msg);
    }

    /**
     * RECORDS
     * =======
     */

    /**
     * @param int|null $expected Number of records expected. Null for any number, but not none
     * @param string|array|ExpressionInterface $tables
     * @param string|array|ExpressionInterface|null $condition
     * @param array|null $params
     * @param string $message
     *
     * @return void
     * @since 1.15
     */
    public static function assertRecordCount(?int $expected, $tables, $condition = null, ?array $params = [], string $message = ''): void
    {
        $count = static::dbCount($tables, $condition, $params ?? []);

        if ($expected === null) {
            static::assertGreaterThan(0, $count, $message);
        } else {
            static::assertEquals($expected, $count, $message);
        }
    }

    /**
     * @param string|array|ExpressionInterface $tables
     * @param string|array|ExpressionInterface|null $condition
     * @param array|null $params
     * @param string $message
     *
     * @return void
     * @since 1.15
     */
    public static function assertRecordExists($tables, $condition = null, ?array $params = [], string $message = 'Record does not exist'): void
    {
        static::assertRecordCount(1, $tables, $condition, $params ?? [], $message);
    }

    /**
     * @param string|array|ExpressionInterface $tables
     * @param string|array|ExpressionInterface|null $condition
     * @param array|null $params
     * @param string $message
     *
     * @return void
     * @since 1.15
     */
    public static function assertRecordNotExists($tables, $condition = null, ?array $params = [], string $message = 'Record exists'): void
    {
        static::assertRecordCount(0, $tables, $condition, $params ?? [], $message);
    }

    /**
     * @param string|array|ExpressionInterface $tables
     * @param string|array|ExpressionInterface|null $condition
     * @param array|null $params
     * @param string $message
     *
     * @return void
     * @since 1.15
     */
    public static function assertRecordExistsAny($tables, $condition = null, ?array $params = [], string $message = 'Record does not exist'): void
    {
        static::assertRecordCount(null, $tables, $condition, $params ?? [], $message);
    }

    /**
     * @param int|string|null $expected Number of records expected. Null for any number, but not none
     * @param string $column
     * @param string|array|ExpressionInterface $tables
     * @param string|array|ExpressionInterface|null $condition
     * @param array|null $params
     * @param string $message
     *
     * @return void
     * @since 1.15
     */
    public static function assertRecordValue($expected, string $column, $tables, $condition = null, ?array $params = [], string $message = ''): void
    {
        $value = self::dbQuery($tables, $condition, $params, 1)->select($column)->scalar();
        static::assertEquals($expected, $value, $message);
    }

    public static function assertUUID($value, bool $allowNull = false, bool $strict = false, $message = '')
    {
        if ($allowNull && $value === null) {
            return;
        }

        // validate UUID without changing the input (other than trimming)
        $uuid = UUID::validate($value, null, null, null);

        static::assertNotNull($uuid, $message);

        if ($strict) {
            static::assertEquals($uuid, $value, $message);
        }
    }

    public static function assertNotUUID($value, $message = '')
    {
        // validate UUID without changing the input (other than trimming)
        $uuid = UUID::validate($value, null, null, null);

        static::assertNull($uuid, $message);
    }

    public function expectExceptionTypeError(string $calledClass, string $method, int $argumentNumber, string $argumentName, string $expectedType, string $givenTye, string $exceptionClass = TypeError::class): void
    {
        $this->expectException($exceptionClass);

        $calledClass = str_replace('\\', '\\\\', $calledClass);
        $argumentName = ltrim($argumentName, '$');

        $this->expectExceptionMessageRegExp(
            sprintf(
                // Php < 8 uses: "Argument n passed to class::method() ..."
                // PHP > 7 uses: "class::method(): Argument #n ($argument) ..."
                '@^((Argument %d passed to )?%s::%s\\(\\)(?(2)|: Argument #%d \\(\\$%s\\))) must be of( the)? type %s, %s given, called in /.*@',
                $argumentNumber,
                $calledClass,
                $method,
                $argumentNumber,
                $argumentName,
                $expectedType,
                $givenTye,
            ),
        );
    }


    /**
     * EVENTS HELPERS
     * ==============
     */

    /**
     * Saves the events to be later asserted.
     *
     * You may want to override this method to add some specific data to be verified later. E.g., like so:
     * ```
     *  public function handleEvent(Event $event)
     *  {
     *      $e = [];
     *
     *      if ($event instanceof EventA) {
     *          $e['extra'] = [$event->name => get_debug_type($event->data)];
     *      }
     *
     *      if ($event instanceof EventB) {
     *          $e['extra'] = array_column($event->data, 'name', 'id');
     *      }
     *
     *      parent::handleEvent($event, $e);
     *  }
     * ```
     *
     * @since 1.15
     * @see static::assertEvents()
     */
    public function handleEvent(Event $event, array $eventData = [])
    {
        $eventData = ArrayHelper::merge([
            'class' => get_class($event),
            'event' => $event->name,
            'sender' => $event->sender,
            'data' => $event->data,
            'handled' => $event->handled,
        ], $eventData);

        $this->firedEvents[] = $eventData;
    }

    /**
     * LOGGING HELPERS
     * ===============
     */

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
     *  - optionally call `static::logReset();` when you no longer need the log to be captured within the test. Though,
     * this will automatically be called during `tearDown()`.
     *
     *  If you need all log entries, call `static::logFilterMessageTexts()` with no arguments, or if you want the
     * complete log information, use `static::logFilterMessages()`. For the format of the returned array elements to
     * the latter, please see `\yii\log\Logger::$messages`.
     *
     *  For some kind of sample implementation, please see `\humhub\tests\codeception\unit\LogAssertionsSelfTest`
     * **********************************************************************************************************************
     *
     * @throws InvalidConfigException
     * @since 1.15
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
        Yii::$app->set(
            'log',
            Yii::createObject(['class' => Dispatcher::class, 'targets' => [static::class => self::$logTarget]]),
        );
    }

    /**
     * Flush the captured log entries without stopping to capture them
     *
     * @since 1.15
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
     * @since 1.15
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
     *                                  Logger::LEVEL_ERROR, Logger::LEVEL_WARNING, Logger::LEVEL_INFO,
     *     Logger::LEVEL_TRACE
     * @param string[]|null $categories Array of categories to be returned or null for any
     * @param string[]|null $exceptCategories Array of categories NOT to be returned, or null for no exclusion
     *
     * @return array of message, following the format of `\yii\log\Logger::$messages`
     * @throws ErrorException
     * @since 1.15
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
     * @since 1.15
     * @see static::logInitialize()
     * @see static::logFilterMessages()
     */
    public static function logFilterMessageTexts($levels = null, ?array $categories = null, ?array $exceptCategories = null): array
    {
        return array_column(static::logFilterMessages($levels, $categories, $exceptCategories), 0);
    }

    /**
     * @see \yii\db\Connection::createCommand()
     * @since 1.15
     * @deprecated since 1.15
     * @internal
     */
    public static function dbCommand($sql = null, $params = []): Command
    {
        return Yii::$app->getDb()->createCommand($sql, $params);
    }

    /**
     * @param Command $cmd
     * @param bool $execute
     *
     * @return Command
     * @throws \yii\db\Exception
     * @since 1.15
     * @deprecated since 1.15
     * @internal
     */
    protected static function dbCommandExecute(Command $cmd, bool $execute = true): Command
    {
        if ($execute) {
            $cmd->execute();
        }

        return $cmd;
    }

    /**
     * @see Query
     * @since 1.15
     * @deprecated since 1.15
     * @internal
     */
    public static function dbQuery($tables, $condition, $params = [], $limit = 10): Query
    {
        return (new Query())
            ->from($tables)
            ->where($condition, $params)
            ->limit($limit);
    }

    /**
     * @see Command::insert
     * @since 1.15
     * @deprecated since 1.15
     * @internal
     */
    public static function dbInsert($table, $columns, bool $execute = true): Command
    {
        return static::dbCommandExecute(static::dbCommand()->insert($table, $columns), $execute);
    }

    /**
     * @see Command::update
     * @since 1.15
     * @deprecated since 1.15
     * @internal
     */
    public static function dbUpdate($table, $columns, $condition = '', $params = [], bool $execute = true): Command
    {
        return static::dbCommandExecute(static::dbCommand()->update($table, $columns, $condition, $params), $execute);
    }

    /**
     * @see Command::upsert
     * @since 1.15
     * @deprecated since 1.15
     * @internal
     */
    public static function dbUpsert($table, $insertColumns, $updateColumns = true, $params = [], bool $execute = true): Command
    {
        return static::dbCommandExecute(
            static::dbCommand()->upsert($table, $insertColumns, $updateColumns, $params),
            $execute,
        );
    }

    /**
     * @see Command::delete()
     * @since 1.15
     * @deprecated since 1.15
     * @internal
     */
    public static function dbDelete($table, $condition = '', $params = [], bool $execute = true): Command
    {
        return static::dbCommandExecute(static::dbCommand()->delete($table, $condition, $params), $execute);
    }

    /**
     * @see Query::select
     * @see Query::from
     * @see Query::where
     * @see \yii\db\QueryTrait::limit()
     * @since 1.15
     * @deprecated since 1.15
     * @internal
     */
    public static function dbSelect($tables, $columns, $condition = '', $params = [], $limit = 10, $selectOption = null): array
    {
        return static::dbQuery($tables, $condition, $params, $limit)
            ->select($columns, $selectOption)
            ->all();
    }

    /**
     * @see Command::delete()
     * @since 1.15
     * @deprecated since 1.15
     * @internal
     */
    public static function dbCount($tables, $condition = '', $params = [])
    {
        return static::dbQuery($tables, $condition, $params)
            ->select("count(*)")
            ->scalar();
    }
}
