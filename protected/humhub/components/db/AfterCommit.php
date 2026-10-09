<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\components\db;

use Throwable;
use WeakMap;
use Yii;
use yii\base\Event;
use yii\db\Connection;

/**
 * Runs a callback after the commit of the open transaction, or at once without one.
 *
 * Meant for side effects that hand records to other processes, e.g. a queue job: a worker on
 * another connection could run the job before the commit and not find the records yet.
 *
 * - The callbacks run once, after the outermost commit, in the order they were registered.
 *   A commit of a nested transaction (a savepoint) does not run them.
 * - A rollback of the outermost transaction drops them; a rollback to a savepoint does not.
 * - A failing callback is logged and does not stop the others.
 * - Callbacks left over from a transaction that ended without a commit or rollback event (e.g. a
 *   closed connection) are dropped when the connection begins its next transaction.
 *
 * ```php
 * AfterCommit::run(fn() => Yii::$app->queue->push($job));
 * ```
 *
 * @since 1.20
 */
final class AfterCommit
{
    /**
     * @var bool whether callbacks run at once, ignoring an open transaction.
     *
     * Set by the test harness when it wraps each test into a transaction that is never committed.
     * @internal
     */
    public static bool $immediate = false;

    /**
     * @var WeakMap<Connection, callable[]>|null the pending callbacks by connection
     */
    private static ?WeakMap $pending = null;

    /**
     * Runs the callback after the commit of the open transaction of the connection (default:
     * `Yii::$app->db`), or at once when no transaction is open.
     */
    public static function run(callable $callback, ?Connection $db = null): void
    {
        $db ??= Yii::$app->db;

        if (static::$immediate || !$db->getTransaction()?->getIsActive()) {
            // Whatever is still pending belongs to a transaction that is gone.
            self::release($db);
            $callback();
            return;
        }

        self::$pending ??= new WeakMap();
        if (!isset(self::$pending[$db])) {
            self::$pending[$db] = [];
            $db->on(Connection::EVENT_COMMIT_TRANSACTION, [self::class, 'onCommit']);
            $db->on(Connection::EVENT_ROLLBACK_TRANSACTION, [self::class, 'onRollback']);
            $db->on(Connection::EVENT_BEGIN_TRANSACTION, [self::class, 'onBegin']);
        }
        self::$pending[$db][] = $callback;
    }

    /**
     * @internal
     */
    public static function onCommit(Event $event): void
    {
        foreach (self::release($event->sender) as $callback) {
            try {
                $callback();
            } catch (Throwable $e) {
                Yii::error('After commit callback failed: ' . $e, 'db');
            }
        }
    }

    /**
     * @internal
     */
    public static function onRollback(Event $event): void
    {
        self::release($event->sender);
    }

    /**
     * A new outermost transaction begins while callbacks are pending: their transaction ended
     * without a commit or rollback event, they are stale.
     *
     * @internal
     */
    public static function onBegin(Event $event): void
    {
        if (!empty(self::release($event->sender))) {
            Yii::warning('Dropped after commit callbacks of a transaction that ended without commit or rollback.', 'db');
        }
    }

    /**
     * Unregisters the handlers of the connection and returns its pending callbacks.
     *
     * @return callable[]
     */
    private static function release(Connection $db): array
    {
        if (self::$pending === null || !isset(self::$pending[$db])) {
            return [];
        }

        $db->off(Connection::EVENT_COMMIT_TRANSACTION, [self::class, 'onCommit']);
        $db->off(Connection::EVENT_ROLLBACK_TRANSACTION, [self::class, 'onRollback']);
        $db->off(Connection::EVENT_BEGIN_TRANSACTION, [self::class, 'onBegin']);

        $callbacks = self::$pending[$db];
        unset(self::$pending[$db]);

        return $callbacks;
    }
}
