<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\notification\components;

use humhub\helpers\Html;
use humhub\models\RecordMap;
use humhub\modules\content\components\ContentAddonActiveRecord;
use humhub\modules\content\helpers\ContentHelper;
use humhub\modules\content\interfaces\ContentOwner;
use humhub\modules\content\models\Content;
use humhub\modules\content\models\ContentContainer;
use humhub\modules\notification\models\Notification;
use humhub\modules\notification\services\GroupingService;
use humhub\modules\space\models\Space;
use humhub\modules\user\models\User;
use Yii;
use yii\base\BaseObject;
use yii\base\InvalidArgumentException;
use yii\db\ActiveRecord;
use yii\db\IntegrityException;
use yii\helpers\Json;
use yii\helpers\Url;

/**
 * A notification bound to its {@see Notification} record.
 *
 * Instances are not created directly but by {@see NotificationManager::load()}, which reads the
 * record (or, for a group, its newest member) and resolves its references: the recipient, the
 * originator, the content, the content container and any other source record through the
 * {@see RecordMap}. All of them are exposed as readonly properties.
 *
 * A notification renders one sentence through {@see getMessage()}, which receives the message
 * parameters of the channel it is rendered for:
 *
 * - `displayName` - the originator, or `''` without one
 * - `displayNames` - the originator and the other members of a group (`''` below two members)
 * - `groupCount` - the size of the group, `1` when not grouped
 * - `namedCount` - how many people `displayNames` names: `0` when it is empty, `1` when a group
 *   collapses to one person (e.g. one originator's notifications), `2` for "A and B" as well as
 *   "A, B and 2 more"
 * - `content`, `contentTitle` - only when the notification is about a content; `[Deleted]` when
 *   the content's record is gone
 *
 * {@see asWeb()} and {@see asMailHtml()} pass HTML-encoded names wrapped in `<strong>`,
 * {@see asMailText()} the plain names and {@see asPush()} the plain text without any markup.
 *
 * Subclasses declare their {@see group()}, which settles the default {@see priority()}, and
 * whether they are {@see listed()} in the web list.
 *
 * @since 1.20
 */
abstract class BaseNotification extends BaseObject
{
    public readonly Notification $record;
    public readonly User $recipient;
    public readonly ?User $originator;
    public readonly ?Content $content;
    public readonly ?ContentContainer $contentContainer;
    public readonly ?ActiveRecord $sourceRecord;
    public readonly array $payload;
    public readonly int $groupCount;

    /**
     * @var int minimum members of a group
     */
    public int $groupingThreshold = 2;

    /**
     * @var int length of the time bucket the members of a group are created in
     */
    public int $groupingTimeBucketSeconds = 900;

    /**
     * @var int max length of the content preview in the web list
     */
    public int $webContentLength = 60;

    /**
     * @var int max length of the content preview in mails
     */
    public int $mailContentLength = 300;

    private ?GroupingService $_groupingService = null;

    /**
     * @throws IntegrityException when the source record is gone
     */
    public function __construct(Notification $record, $config = [])
    {
        $this->record = $record;
        $this->recipient = $record->user;
        $this->originator = $record->originator;
        $this->content = $record->content;
        $this->contentContainer = $record->contentContainer;
        $this->sourceRecord = self::resolveSourceRecord($record);
        $this->payload = self::decodePayload($record);
        $this->groupCount = $record->group_count ?? 1;

        // last, so that init() of a subclass can read the record-bound properties
        parent::__construct($config);
    }

    /**
     * @throws IntegrityException when `source_record_id` is set but the record is gone or its class unavailable
     */
    private static function resolveSourceRecord(Notification $record): ?ActiveRecord
    {
        if ($record->source_record_id === null) {
            return null;
        }

        $source = RecordMap::getById((int)$record->source_record_id, ActiveRecord::class);
        if ($source === null) {
            throw new IntegrityException(
                'Source record of notification #' . $record->id . ' no longer exists or its class is unavailable',
            );
        }

        return $source;
    }

    private static function decodePayload(Notification $record): array
    {
        $payload = $record->payload;
        if (is_array($payload)) {
            return $payload;
        }
        if ($payload === null || $payload === '') {
            return [];
        }

        try {
            $decoded = Json::decode((string)$payload);
        } catch (InvalidArgumentException $e) {
            Yii::warning('Invalid payload of notification #' . $record->id . ': ' . $e->getMessage(), 'notification');
            return [];
        }

        return is_array($decoded) ? $decoded : [];
    }

    /**
     * The group the user switches this notification on or off with.
     */
    abstract public static function group(): NotificationGroup;

    /**
     * The sentence of the notification, built from the message parameters of a channel.
     */
    abstract protected function getMessage(array $params): string;

    public static function priority(): NotificationPriority
    {
        return static::group()->priority;
    }

    /**
     * Whether the notification appears in the web list.
     */
    public static function listed(): bool
    {
        return true;
    }

    final public function asWeb(): string
    {
        return $this->getMessage($this->getMessageParamsWeb());
    }

    final public function asMailHtml(): string
    {
        return $this->getMessage($this->getMessageParamsMailHtml());
    }

    final public function asMailText(): string
    {
        return $this->getMessage($this->getMessageParamsMailText());
    }

    /**
     * The plain text sentence with the short content preview of the web list.
     */
    final public function asPush(): string
    {
        return $this->getMessage($this->getMessageParamsPlain($this->webContentLength));
    }

    /**
     * The plain text sentence with the short content preview of the web list.
     */
    public function getMailSubject(): string
    {
        return $this->getMessage($this->getMessageParamsPlain($this->webContentLength));
    }

    /**
     * The target of the notification: a content addon (e.g. a comment), the content, or the container.
     */
    public function getUrl(bool $scheme = false): ?string
    {
        if ($this->sourceRecord instanceof ContentAddonActiveRecord) {
            return $this->sourceRecord->getUrl($scheme);
        }

        if ($this->content !== null) {
            return $this->content->getUrl($scheme);
        }

        return $this->contentContainer?->polymorphicRelation?->getUrl($scheme);
    }

    /**
     * The absolute `notification/entry` URL, which marks the group seen and redirects to {@see getUrl()}.
     */
    final public function getEntryUrl(): string
    {
        return Url::to(['/notification/entry', 'id' => $this->record->id], true);
    }

    /**
     * @return array<array{label: string, url: string}>
     */
    public function getMailActions(): array
    {
        return [
            ['label' => Yii::t('NotificationModule.base', 'View online'), 'url' => $this->getEntryUrl()],
        ];
    }

    /**
     * Additional plain text shown under the sentence in the mail, e.g. a message the originator
     * wrote; encoded by the view. `null` for none.
     *
     * @since 1.20
     */
    public function getMailBody(): ?string
    {
        return null;
    }

    /**
     * The content record whose preview a mail shows, if any.
     */
    public function getMailContentRecord(): ?ContentOwner
    {
        return $this->getContentOwner();
    }

    /**
     * The record the sentence is about: the content's record by default. A subclass whose sentence
     * is about another record (a mention in a comment, a liked comment) overrides it to return
     * e.g. {@see $sourceRecord} when that is a {@see ContentOwner}.
     */
    protected function getContentOwner(): ?ContentOwner
    {
        $record = $this->content?->getPolymorphicRelation();

        return $record instanceof ContentOwner ? $record : null;
    }

    /**
     * The space the notification is about: by default its container, if that is a space. A
     * notification without a container (e.g. about a space membership) overrides it to name the
     * space, which also files it under that space in a mail of several notifications.
     *
     * @since 1.20
     */
    public function getSpace(): ?Space
    {
        $container = $this->contentContainer?->polymorphicRelation;

        return $container instanceof Space ? $container : null;
    }

    /**
     * Whether the given user may receive this notification.
     */
    public function canReceive(User $user): bool
    {
        return true;
    }

    /**
     * The query of the notifications this one is grouped with; `null` for an ungrouped notification.
     */
    public function getGroupingQuery(): ?ActiveQueryNotification
    {
        return null;
    }

    public function getGroupingService(): GroupingService
    {
        return $this->_groupingService ??= new GroupingService($this);
    }

    /**
     * The message parameters of the plain-text channels (mail text, mail subject, push): all
     * values are plain text, nothing is HTML-encoded. The base of every channel's parameters -
     * a subclass adds its own parameters here.
     *
     * @param int $maxLength max length of the content preview
     */
    protected function getMessageParamsPlain(int $maxLength): array
    {
        [$displayNames, $namedCount] = $this->resolveDisplayNames(fn(string $name): string => $name);

        return array_merge([
            'displayName' => $this->originator?->displayName ?? '',
            'displayNames' => $displayNames,
            'namedCount' => $namedCount,
            'groupCount' => $this->groupCount,
        ], $this->getContentParams($maxLength, fn(string $info): string => $info, true));
    }

    /**
     * The plain-text parameters with the long content preview of mails.
     */
    protected function getMessageParamsMailText(): array
    {
        return $this->getMessageParamsPlain($this->mailContentLength);
    }

    protected function getMessageParamsWeb(): array
    {
        $encodeStrong = fn(string $name): string => Html::strong(Html::encode($name));

        return array_merge($this->getMessageParamsPlain($this->webContentLength), [
            'displayName' => $this->originator ? $encodeStrong($this->originator->displayName) : '',
            'displayNames' => $this->formatDisplayNames($encodeStrong),
        ], $this->getContentParams($this->webContentLength, fn(string $info): string => $info));
    }

    protected function getMessageParamsMailHtml(): array
    {
        return array_merge(
            $this->getMessageParamsWeb(),
            $this->getContentParams($this->mailContentLength, fn(string $info): string => Html::strong($info)),
        );
    }

    /**
     * `content` and `contentTitle` of {@see getContentOwner()}; `[Deleted]` for both when the
     * notification is about a content whose record is gone; `[]` when it is about no content.
     *
     * @param bool $plain whether to decode the HTML entities of the (encoded) content info
     */
    protected function getContentParams(int $maxLength, callable $contentFormatter, bool $plain = false): array
    {
        $owner = $this->getContentOwner();
        if ($owner === null) {
            if ($this->record->content_id === null) {
                return [];
            }

            $deleted = Yii::t('NotificationModule.base', '[Deleted]');

            return ['content' => $deleted, 'contentTitle' => $deleted];
        }

        $params = [
            'content' => $contentFormatter(ContentHelper::getContentInfo($owner, true, $maxLength)),
            'contentTitle' => ContentHelper::getContentInfo($owner, false, $maxLength),
        ];

        return $plain
            ? array_map(fn(string $info): string => html_entity_decode($info, ENT_QUOTES | ENT_HTML5), $params)
            : $params;
    }

    /**
     * The originator and the other members of a group, e.g. "Anna, Ben and 2 more"; `''` below two
     * members. Without an originator, the others only.
     */
    protected function formatDisplayNames(callable $formatter): string
    {
        return $this->resolveDisplayNames($formatter)[0];
    }

    /**
     * {@see formatDisplayNames()} and the number of people it names (0, 1 or 2).
     *
     * @return array{0: string, 1: int}
     */
    private function resolveDisplayNames(callable $formatter): array
    {
        if ($this->groupCount < 2) {
            return ['', 0];
        }

        $grouping = $this->getGroupingService();
        $names = [];
        $total = $grouping->countOtherGroupedUsers();
        if ($this->originator !== null) {
            $names[] = $formatter($this->originator->displayName);
            $total++;
        }
        foreach ($grouping->getOtherGroupedUsers() as $user) {
            $names[] = $formatter($user->displayName);
        }

        if ($names === []) {
            return ['', 0];
        }

        if (count($names) === 1 || $total < 2) {
            // A group of one originator's notifications (or one whose other originators are not
            // visible): the phrase is that person, as a grouped message renders `{displayNames}` only.
            return [$names[0], 1];
        }

        if ($total === 2) {
            return [Yii::t('NotificationModule.base', '{displayName1} and {displayName2}', [
                'displayName1' => $names[0],
                'displayName2' => $names[1],
            ]), 2];
        }

        return [Yii::t('NotificationModule.base', '{displayName1}, {displayName2} and {count} more', [
            'displayName1' => $names[0],
            'displayName2' => $names[1],
            'count' => $total - 2,
        ]), 2];
    }
}
