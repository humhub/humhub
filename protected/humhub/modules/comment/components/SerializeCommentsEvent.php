<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\comment\components;

use humhub\modules\comment\models\Comment;
use yii\base\Event;

/**
 * Fired once per serialized comment batch by {@see \humhub\modules\comment\services\CommentJsonService}
 * (its `EVENT_SERIALIZE_COMMENTS`), letting modules attach namespaced extension data to
 * individual comments — one query for the whole batch instead of one per comment.
 *
 * A module attaches in its `config.php`:
 *
 * ```php
 * 'events' => [
 *     [CommentJsonService::class, CommentJsonService::EVENT_SERIALIZE_COMMENTS, [Events::class, 'onSerializeComments']],
 * ],
 * ```
 *
 * ```php
 * public static function onSerializeComments(SerializeCommentsEvent $event): void
 * {
 *     foreach ($event->comments as $comment) {
 *         $event->addData($comment->id, 'reportcontent', ['reported' => ReportContent::isReported($comment)]);
 *     }
 * }
 * ```
 *
 * Each serialized comment then carries the accumulated result under its own `extensions`
 * key, keyed by namespace — e.g. `$data['extensions']['reportcontent']`.
 *
 * @since 1.19
 */
class SerializeCommentsEvent extends Event
{
    /**
     * @var Comment[] every comment in this batch — window roots plus one level of loaded
     *      child-preview replies, or a single comment for the create/update/info paths
     */
    public array $comments = [];

    /**
     * @var array<int, array<string, array>> commentId => namespace => data
     *
     * Named distinctly from the parent {@see Event::$data} (a public, untyped property
     * for arbitrary caller data) to avoid colliding with it - PHP forbids narrowing an
     * inherited public property's visibility.
     */
    private array $namespacedData = [];

    /**
     * Accumulates `$data` for `$commentId` under `$namespace`. Safe to call multiple times
     * for the same comment with different namespaces; calling it twice for the same
     * (commentId, namespace) pair overwrites the earlier value.
     */
    public function addData(int $commentId, string $namespace, array $data): void
    {
        $this->namespacedData[$commentId][$namespace] = $data;
    }

    /**
     * @return array<string, array> namespace => data attached to `$commentId`, or an empty
     *         array when no handler attached anything to it
     */
    public function getData(int $commentId): array
    {
        return $this->namespacedData[$commentId] ?? [];
    }
}
