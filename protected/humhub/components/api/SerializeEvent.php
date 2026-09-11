<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\components\api;

use humhub\components\ActiveRecord;
use yii\base\Event;

/**
 * Fired once per API response for each batch of serialized records of one type, letting
 * modules attach namespaced data to individual records — one query for the whole batch
 * instead of one per record, and no walking of the response tree.
 *
 * A module attaches in its `config.php`:
 *
 * ```php
 * 'events' => [
 *     [
 *         'class' => SerializeEvent::class,
 *         'event' => SerializeEvent::EVENT_SERIALIZE,
 *         'callback' => [Events::class, 'onApiSerialize'],
 *     ],
 * ],
 * ```
 *
 * ```php
 * public static function onApiSerialize(SerializeEvent $event): void
 * {
 *     if ($event->type !== Comment::class) {
 *         return;
 *     }
 *     foreach ($event->records as $comment) {
 *         $event->addData($comment->id, 'reportcontent', ['reported' => ReportContent::isReported($comment)]);
 *     }
 * }
 * ```
 *
 * The serialized record then carries the accumulated result under its own `extensions` key,
 * keyed by namespace. Since the event name is shared across record types, a handler must
 * filter on {@see self::$type}.
 *
 * The client-side counterpart is the Vue extension-slot mechanism, which renders components
 * from that data — see `docs/develop/ui-js-vuejs-extensions.md`.
 *
 * @since 1.20
 */
class SerializeEvent extends Event
{
    public const EVENT_SERIALIZE = 'apiSerialize';

    /**
     * @var string model class of every record in this batch (e.g. `Comment::class`)
     */
    public string $type = '';

    /**
     * @var ActiveRecord[] every record of this batch — e.g. a comment window's roots plus
     *      their loaded reply previews, or a single record for view/create/update responses
     */
    public array $records = [];

    /**
     * @var array<int, array<string, array>> recordId => namespace => data
     *
     * Named distinctly from the parent {@see Event::$data} (a public, untyped property for
     * arbitrary caller data) to avoid colliding with it.
     */
    private array $namespacedData = [];

    /**
     * Fires the event once for `$records` and collapses the accumulated
     * {@see self::addData()} calls into a plain `recordId => namespace => data` map.
     *
     * @param ActiveRecord[] $records
     * @return array<int, array<string, array>>
     */
    public static function collectFor(string $type, array $records): array
    {
        $event = new static();
        $event->type = $type;
        $event->records = $records;
        Event::trigger(static::class, static::EVENT_SERIALIZE, $event);

        $data = [];
        foreach ($records as $record) {
            $data[$record->id] = $event->getData($record->id);
        }

        return $data;
    }

    /**
     * Accumulates `$data` for `$recordId` under `$namespace`. Safe to call multiple times for
     * the same record with different namespaces; calling it twice for the same
     * (recordId, namespace) pair overwrites the earlier value.
     */
    public function addData(int $recordId, string $namespace, array $data): void
    {
        $this->namespacedData[$recordId][$namespace] = $data;
    }

    /**
     * @return array<string, array> namespace => data attached to `$recordId`, or an empty
     *         array when no handler attached anything to it
     */
    public function getData(int $recordId): array
    {
        return $this->namespacedData[$recordId] ?? [];
    }
}
