<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\notification\targets;

use humhub\components\message\MessageFormat;
use humhub\helpers\Html;
use humhub\modules\notification\components\BaseNotification;
use humhub\modules\notification\components\NotificationBlock;
use humhub\modules\notification\components\NotificationContext;
use humhub\modules\notification\components\NotificationPriority;
use humhub\modules\notification\components\NotificationPrivacy;
use humhub\modules\notification\services\NotificationListService;
use humhub\modules\user\models\User;
use Yii;
use yii\base\InvalidArgumentException;
use yii\helpers\Url;

/**
 * One message of a channel: one or several notifications of one recipient, handed to
 * {@see BaseTarget::deliver()}.
 *
 * A {@see BaseNotification::standalone()} notification is always alone in its batch.
 *
 * The batch renders what the channel may show under the network's {@see NotificationPrivacy}:
 * {@see getSubject()}, {@see getPushBody()}, {@see getSentence()} and {@see getBlocks()} hide
 * the content (and, with {@see NotificationPrivacy::Minimal}, the names) themselves, so a channel
 * that uses them needs no checks of its own. A channel showing more than these (e.g. the Space of
 * a notification) checks {@see $privacy} first.
 *
 * @api for channel providers
 * @since 1.20
 */
final readonly class DeliveryBatch
{
    /**
     * @var NotificationPrivacy how much the messages may show, {@see NotificationPrivacy::current()}
     */
    public NotificationPrivacy $privacy;

    /**
     * @param BaseNotification[] $notifications one or several, all for $recipient
     * @param string $channel the id of the target the batch is delivered through
     * @param NotificationPrivacy|null $privacy the network's level ({@see NotificationPrivacy::current()}) when `null`
     * @internal constructed by the core
     */
    public function __construct(
        public User $recipient,
        public array $notifications,
        public string $channel,
        ?NotificationPrivacy $privacy = null,
    ) {
        if ($notifications === []) {
            throw new InvalidArgumentException('A delivery batch needs at least one notification.');
        }
        $this->privacy = $privacy ?? NotificationPrivacy::current();
    }

    /**
     * Where the notifications are rendered: the channel and the {@see $privacy}.
     */
    public function getContext(): NotificationContext
    {
        return new NotificationContext($this->channel, $this->privacy);
    }

    public function isSingle(): bool
    {
        return count($this->notifications) === 1;
    }

    /**
     * One line. One notification: its own subject ({@see BaseNotification::asMailSubject()}); without
     * content its sentence without content ({@see BaseNotification::asRedactedText()}); minimal
     * "New notification: {category}". Several: "{count} new notifications".
     */
    public function getSubject(): string
    {
        if (!$this->isSingle()) {
            return $this->getCountSentence();
        }

        $notification = $this->first();
        $subject = match ($this->privacy) {
            NotificationPrivacy::Full => $notification->asMailSubject(),
            NotificationPrivacy::Redacted => $notification->asRedactedText(),
            NotificationPrivacy::Minimal => $this->getMinimalSentence($notification, MessageFormat::Text),
        };

        return preg_replace('/\s*\R\s*/', ' ', trim($subject));
    }

    /**
     * One notification: {@see BaseNotification::asPush()}, without content its sentence without
     * content. Several: the subject. Minimal: "{count} new notifications", also for one.
     */
    public function getPushBody(): string
    {
        return match (true) {
            $this->privacy === NotificationPrivacy::Minimal => $this->getCountSentence(),
            !$this->isSingle() => $this->getSubject(),
            $this->privacy === NotificationPrivacy::Redacted => $this->first()->asRedactedText(),
            default => $this->first()->asPush(),
        };
    }

    /**
     * The sentence of one notification of the batch as a mail shows it: the sentence with the long
     * content preview ({@see BaseNotification::asMailHtml()}/{@see BaseNotification::asMailText()}),
     * without content its sentence without content, minimal "New notification: {category}".
     */
    public function getSentence(BaseNotification $notification, MessageFormat $format = MessageFormat::Text): string
    {
        $html = $format === MessageFormat::Html;

        return match ($this->privacy) {
            NotificationPrivacy::Full => $html ? $notification->asMailHtml() : $notification->asMailText(),
            NotificationPrivacy::Redacted => $html ? $notification->asRedactedHtml() : $notification->asRedactedText(),
            NotificationPrivacy::Minimal => $this->getMinimalSentence($notification, $format),
        };
    }

    /**
     * The blocks shown under the sentence of a single notification: its
     * {@see BaseNotification::getRenderedBlocks()}; without content only the "View online"
     * button (when the notification has a {@see BaseNotification::getUrl()}).
     *
     * @return NotificationBlock[]
     */
    public function getBlocks(BaseNotification $notification): array
    {
        return $this->privacy === NotificationPrivacy::Full
            ? $notification->getRenderedBlocks($this->getContext())
            : $notification->getViewOnlineBlocks();
    }

    /**
     * A line per category with the number of its notifications, e.g. "Reactions on my content:
     * 3 new notifications", in the order the categories first appear - what a minimal message of
     * several notifications lists. Plain text.
     *
     * @return string[]
     */
    public function getCategorySummary(): array
    {
        $counts = [];
        foreach ($this->notifications as $notification) {
            $category = $notification::category();
            $counts[$category->id] ??= ['category' => $category->title, 'count' => 0];
            $counts[$category->id]['count']++;
        }

        return array_values(array_map(static fn(array $count): string => Yii::t(
            'NotificationModule.base',
            '{category}: {count,plural,one{# new notification} other{# new notifications}}',
            $count,
        ), $counts));
    }

    /**
     * One: the entry URL; several: the notification overview, absolute.
     */
    public function getUrl(): string
    {
        return $this->isSingle() ? $this->first()->getEntryUrl() : Url::to(['/notification/overview'], true);
    }

    /**
     * Identifies what a newer push message may replace on the device.
     *
     * One notification: `<channel>:<recipient id>:<grouping key>` - a newer message about the same
     * group ("Jane and 3 more liked …") replaces the older one, while unrelated notifications stay
     * side by side. Several: `<channel>:<recipient id>` - a newer summary replaces the older one.
     */
    public function getCollapseKey(): string
    {
        $key = $this->channel . ':' . $this->recipient->id;

        return $this->isSingle() ? $key . ':' . (int)$this->first()->record->grouping_key : $key;
    }

    /**
     * Whether any notification has the high priority.
     */
    public function isHighPriority(): bool
    {
        foreach ($this->notifications as $notification) {
            if ((int)$notification->record->priority === NotificationPriority::High->value) {
                return true;
            }
        }

        return false;
    }

    /**
     * The recipient's number of unseen entries in the web list (groups count once), see
     * {@see NotificationListService::unseenCount()}.
     */
    public function getUnreadCount(): int
    {
        return NotificationListService::unseenCount($this->recipient);
    }

    /**
     * "{count} new notifications".
     */
    private function getCountSentence(): string
    {
        return Yii::t(
            'NotificationModule.base',
            '{count,plural,one{# new notification} other{# new notifications}}',
            ['count' => count($this->notifications)],
        );
    }

    /**
     * "New notification: {category}".
     */
    private function getMinimalSentence(BaseNotification $notification, MessageFormat $format): string
    {
        $category = $notification::category()->title;

        return Yii::t('NotificationModule.base', 'New notification: {category}', [
            'category' => $format === MessageFormat::Html ? Html::encode($category) : $category,
        ]);
    }

    /**
     * The first notification of the batch - the only one of a single batch.
     *
     * @return BaseNotification
     */
    public function first(): BaseNotification
    {
        return $this->notifications[array_key_first($this->notifications)];
    }
}
