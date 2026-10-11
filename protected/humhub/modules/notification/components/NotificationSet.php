<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\notification\components;

use humhub\components\ActiveRecord;
use humhub\modules\notification\events\BeforeDispatchEvent;
use humhub\modules\user\components\ActiveQueryUser;
use humhub\modules\user\models\User;
use yii\base\InvalidArgumentException;

/**
 * Sends several notification classes for one event, each recipient receiving one notification
 * only: the best-matching one.
 *
 * The entries are added in rank order, the most specific first. A recipient is notified by the
 * first entry whose recipients contain them and that writes a notification for them (they pass
 * the filters of {@see BaseNotification::send()} and {@see BaseNotification::canReceive()} of
 * that class); every later entry skips them. A recipient an entry skips - e.g. rejected by its
 * `canReceive()`, or no channel enabled for its class - may still be notified by a later entry.
 *
 * ```php
 * NotificationSet::create(source: $comment, originator: $user)
 *     ->add(MentionedNotification::class, $mentionedUsers)
 *     ->add(CommentReplyNotification::class, $rootCommentAuthor, source: $rootComment)
 *     ->add(NewCommentNotification::class, $followerQuery)
 *     ->send();
 * ```
 *
 * {@see send()} queues one job for the whole set - after the commit of the current transaction of
 * `Yii::$app->db`, like {@see BaseNotification::send()} - which processes the entries in order,
 * so the ranking does not depend on the queue. Recipient queries are resolved when the job runs.
 *
 * {@see NotificationManager::EVENT_BEFORE_DISPATCH} fires once per entry, before the job is
 * queued; a handler that vetoes an entry drops that entry only.
 *
 * @api
 * @since 1.20
 */
final class NotificationSet
{
    /**
     * @var BeforeDispatchEvent[] the entries in rank order
     */
    private array $entries = [];

    private function __construct(
        private readonly ?ActiveRecord $source,
        private readonly ?User $originator,
    ) {
    }

    /**
     * Starts a set about the source by the originator - the defaults of every entry.
     *
     * @param ActiveRecord|null $source what the notifications are about, see {@see BaseNotification::send()}
     * @param User|null $originator the user who caused them
     * @api
     */
    public static function create(?ActiveRecord $source = null, ?User $originator = null): self
    {
        return new self($source, $originator);
    }

    /**
     * Adds the next entry, ranked below those added before.
     *
     * @param class-string<BaseNotification> $class
     * @param ActiveQueryUser|User|int|array<User|int> $recipients as for {@see BaseNotification::send()}
     * @param ActiveRecord|null $source what this notification is about, when it is not the source of the set
     * @param array $payload see {@see BaseNotification::send()}
     * @param bool $notifyOriginator see {@see BaseNotification::send()}
     * @param bool $dedupe see {@see BaseNotification::send()}
     * @throws InvalidArgumentException when the class is no notification class
     * @api
     */
    public function add(
        string $class,
        ActiveQueryUser|User|int|array $recipients,
        ?ActiveRecord $source = null,
        array $payload = [],
        bool $notifyOriginator = false,
        bool $dedupe = true,
    ): self {
        if (!is_subclass_of($class, BaseNotification::class)) {
            throw new InvalidArgumentException("Class {$class} is not a " . BaseNotification::class);
        }

        $this->entries[] = new BeforeDispatchEvent([
            'class' => $class,
            'recipients' => $recipients,
            'source' => $source ?? $this->source,
            'originator' => $this->originator,
            'payload' => $payload,
            'notifyOriginator' => $notifyOriginator,
            'dedupe' => $dedupe,
        ]);

        return $this;
    }

    /**
     * Queues the set - one job for all entries, after the commit of the current transaction.
     * Nothing is queued without entries or when every entry was vetoed.
     *
     * @throws InvalidArgumentException when a source is not saved
     * @api
     */
    public function send(): void
    {
        NotificationManager::dispatchSet($this->entries);
        $this->entries = [];
    }
}
