<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\notification\components;

use humhub\components\ActiveRecord as HumHubActiveRecord;
use humhub\components\message\MessageFormat;
use humhub\components\message\MessageParam;
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
use humhub\modules\user\components\ActiveQueryUser;
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
 * A module's notification class extends this class, implements {@see getMessage()} and is sent
 * with {@see send()}. Everything else has a default: the {@see category()} users switch it with
 * (the module's own one), its {@see priority()}, whether it is {@see listed()} in the web list,
 * its {@see grouping()}, the {@see getMessageParams()} its sentence needs beyond the built-in ones,
 * the {@see getUrl()} it links to, an {@see getExcerpt()}, the {@see getActions()} of a mail, the
 * {@see getSubjectRecord()} the sentence names and the {@see getPreviewRecord()} a mail previews.
 * See `docs/develop/concept-notifications.md`.
 *
 * Instances are not created directly but by {@see NotificationManager::load()}, which reads the
 * record (or, for a group, its newest member) and resolves its references: the recipient, the
 * originator, the content, the content container and any other source record through the
 * {@see RecordMap}. All of them are exposed as readonly properties.
 *
 * The built-in message parameters, rendered for the channel together with those of
 * {@see getMessageParams()}:
 *
 * - `displayName` - the originator, or `''` without one
 * - `displayNames` - the originator and the other members of a group (`''` below two members)
 * - `groupCount` - the size of the group, `1` when not grouped
 * - `namedCount` - how many people `displayNames` names: `0` when it is empty, `1` when a group
 *   collapses to one person (e.g. one originator's notifications), `2` for "A and B" as well as
 *   "A, B and 2 more"
 * - `content`, `contentTitle` - only when the notification is about a content: the
 *   {@see getSubjectRecord()} with and without its type name; `[Deleted]` when the content's
 *   record is gone
 *
 * @since 1.20
 */
abstract class BaseNotification extends BaseObject
{
    /**
     * Max length of the content preview in the web list, a push message and a mail subject.
     */
    private const PREVIEW_LENGTH_SHORT = 60;

    /**
     * Max length of the content preview in a mail.
     */
    private const PREVIEW_LENGTH_LONG = 300;

    /**
     * Max length of the {@see getExcerpt()} in a mail.
     */
    private const EXCERPT_LENGTH = 1000;

    /**
     * @api
     */
    public readonly Notification $record;

    /**
     * @api
     */
    public readonly User $recipient;

    /**
     * @api
     */
    public readonly ?User $originator;

    /**
     * @api
     */
    public readonly ?Content $content;

    /**
     * @api
     */
    public readonly ?ContentContainer $contentContainer;

    /**
     * @var ActiveRecord|null the source record other than a content or a container, e.g. a comment
     * @api
     */
    public readonly ?ActiveRecord $sourceRecord;

    /**
     * @var array the `payload` of {@see send()}
     * @api
     */
    public readonly array $payload;

    /**
     * @var int the size of the group this notification represents, 1 when not grouped
     * @api
     */
    public readonly int $groupCount;

    private ?GroupingService $_groupingService = null;

    /**
     * @throws IntegrityException when the source record is gone
     * @internal use {@see NotificationManager::load()}
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
     * Sends the notification to the recipients - asynchronously, after the current transaction
     * of `Yii::$app->db` is committed.
     *
     * ```php
     * TaskAssignedNotification::send($assignees, source: $task, originator: $user);
     * ```
     *
     * A recipient is skipped when they are not enabled, are the originator (unless `$notifyOriginator`),
     * block or are blocked by the originator, cannot view the content, already have this
     * notification about the source by the originator (unless `$dedupe` is off), or are rejected
     * by {@see canReceive()}.
     *
     * @param ActiveQueryUser|User|int|array<User|int> $recipients a query must be self-contained: it is
     * serialized into the queue, so it cannot be a relation query bound to a `primaryModel`
     * @param HumHubActiveRecord|null $source what the notification is about: a content, a content record (e.g. a
     * post), a content addon (e.g. a comment), a container or any other saved record
     * @param User|null $originator the user who caused the notification
     * @param array $payload data stored with the notification as JSON, see {@see $payload}
     * @param bool $notifyOriginator whether the originator receives it when among the recipients
     * @param bool $dedupe whether a recipient who already has this notification about the source by
     * the originator is skipped; no effect without a source
     * @api
     */
    final public static function send(
        ActiveQueryUser|User|int|array $recipients,
        ?HumHubActiveRecord $source = null,
        ?User $originator = null,
        array $payload = [],
        bool $notifyOriginator = false,
        bool $dedupe = true,
    ): void {
        NotificationManager::dispatch(
            static::class,
            $recipients,
            $source,
            $originator,
            $payload,
            $notifyOriginator,
            $dedupe,
        );
    }

    /**
     * Deletes the notifications of this class - about the source, of the recipient and by the
     * originator, each when given. A notification about a content or a content addon is deleted
     * with it; one about any other record is revoked by the module when it deletes the record.
     *
     * @return int the number of deleted notifications
     * @api
     */
    final public static function revoke(?HumHubActiveRecord $source = null, ?User $user = null, ?User $originator = null): int
    {
        return NotificationManager::delete(static::class, $source, $user, $originator);
    }

    /**
     * Marks the user's notifications of this class about the source seen - with the whole groups
     * they belong to, e.g. when the user opened the record the notification is about.
     *
     * @api
     */
    final public static function markSeen(?HumHubActiveRecord $source, User $user): void
    {
        NotificationManager::markSeen(static::class, $source, $user);
    }

    /**
     * The category the user switches this notification on or off with: by default the module's
     * own one ({@see NotificationCategory::ofModule()}).
     *
     * @api
     */
    public static function category(): NotificationCategory
    {
        return NotificationCategory::ofModule(static::class);
    }

    /**
     * How urgently the notification is delivered: by default the priority of its {@see category()}.
     *
     * @api
     */
    public static function priority(): NotificationPriority
    {
        return static::category()->priority;
    }

    /**
     * Whether the notification appears in the web list - else it is delivered by the other
     * channels (mail, push) only.
     *
     * @api
     */
    public static function listed(): bool
    {
        return true;
    }

    /**
     * Which notifications of this class are shown as one entry, e.g. `Grouping::byContent()`;
     * `null` (the default) for none.
     *
     * @api
     */
    public static function grouping(): ?Grouping
    {
        return null;
    }

    /**
     * The sentence of the notification, e.g.
     * `Yii::t('TasksModule.base', '{displayName} assigned you to {task}.', $params)`.
     *
     * @param array<string, string|int> $params the built-in parameters and those of
     * {@see getMessageParams()}, rendered for the channel: HTML-encoded for the web list and HTML
     * mails, plain text for text mails, mail subjects and push messages
     * @api
     */
    abstract protected function getMessage(array $params): string;

    /**
     * The message parameters this class adds to the built-in ones (and may override them with):
     * a plain string or a {@see MessageParam}, e.g. `['task' => MessageParam::emphasis($task->title)]`.
     * Values are rendered per channel, see {@see getMessage()}.
     *
     * @return array<string, MessageParam|string|int>
     * @api
     */
    protected function getMessageParams(): array
    {
        return [];
    }

    /**
     * The subject of a mail about this notification alone, from the plain text parameters of
     * {@see getMessage()}: the sentence by default.
     *
     * @param array<string, string|int> $params
     * @api
     */
    protected function getMailSubject(array $params): string
    {
        return $this->getMessage($params);
    }

    /**
     * The HTML sentence of the web list.
     *
     * @api for channel providers
     */
    final public function asWeb(): string
    {
        return $this->getMessage($this->renderParams(MessageFormat::Html, self::PREVIEW_LENGTH_SHORT));
    }

    /**
     * The HTML sentence of a mail, with the long content preview.
     *
     * @api for channel providers
     */
    final public function asMailHtml(): string
    {
        return $this->getMessage($this->renderParams(MessageFormat::Html, self::PREVIEW_LENGTH_LONG, true));
    }

    /**
     * The plain text sentence of a mail, with the long content preview.
     *
     * @api for channel providers
     */
    final public function asMailText(): string
    {
        return $this->getMessage($this->renderParams(MessageFormat::Text, self::PREVIEW_LENGTH_LONG));
    }

    /**
     * The plain text sentence with the short content preview of the web list.
     *
     * @api for channel providers
     */
    final public function asPush(): string
    {
        return $this->getMessage($this->renderParams(MessageFormat::Text, self::PREVIEW_LENGTH_SHORT));
    }

    /**
     * The plain text {@see getMailSubject()}, with the short content preview of the web list.
     *
     * @api for channel providers
     */
    final public function asMailSubject(): string
    {
        return $this->getMailSubject($this->renderParams(MessageFormat::Text, self::PREVIEW_LENGTH_SHORT));
    }

    /**
     * The {@see getExcerpt()} for a mail: HTML-encoded with line breaks, or plain text; shortened
     * when long, `null` without an excerpt.
     *
     * @internal
     */
    final public function renderExcerpt(MessageFormat $format): ?string
    {
        $excerpt = $this->getExcerpt();
        if ($excerpt === null || trim($excerpt) === '') {
            return null;
        }

        $rendered = MessageParam::text(trim($excerpt), self::EXCERPT_LENGTH)->render($format);

        return $format === MessageFormat::Html ? nl2br($rendered) : $rendered;
    }

    /**
     * The target of the notification: a content addon (e.g. a comment), the content, or the container.
     *
     * @api
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
     *
     * @api for channel providers
     */
    final public function getEntryUrl(): string
    {
        return Url::to(['/notification/entry', 'id' => $this->record->id], true);
    }

    /**
     * The actions offered outside the web list, e.g. as buttons of a mail: by default "View online",
     * which opens {@see getUrl()} through the {@see getEntryUrl()}. `[]` for none.
     *
     * @return NotificationAction[]
     * @api
     */
    public function getActions(): array
    {
        return [new NotificationAction(Yii::t('NotificationModule.base', 'View online'), $this->getEntryUrl())];
    }

    /**
     * Plain text shown under the sentence, e.g. a message the originator wrote; the channel
     * encodes, breaks and shortens it. `null` (the default) for none.
     *
     * @api
     */
    public function getExcerpt(): ?string
    {
        return null;
    }

    /**
     * The record the sentence names as `content`/`contentTitle`: by default the content's record.
     * A class whose sentence is about another record (a mention in a comment, a liked comment)
     * returns e.g. {@see $sourceRecord} when that is a {@see ContentOwner}.
     *
     * @api
     */
    public function getSubjectRecord(): ?ContentOwner
    {
        $record = $this->content?->getPolymorphicRelation();

        return $record instanceof ContentOwner ? $record : null;
    }

    /**
     * The record a mail previews: by default the {@see getSubjectRecord()}; `null` for no preview.
     *
     * @api
     */
    public function getPreviewRecord(): ?ContentOwner
    {
        return $this->getSubjectRecord();
    }

    /**
     * The space the notification is about: by default its container, if that is a space. A
     * notification without a container (e.g. about a space membership) overrides it to name the
     * space, which also files it under that space in a mail of several notifications.
     *
     * @api
     */
    public function getSpace(): ?Space
    {
        $container = $this->contentContainer?->polymorphicRelation;

        return $container instanceof Space ? $container : null;
    }

    /**
     * Whether the given user may receive this notification - checked for every recipient right
     * after the notification was written for them.
     *
     * @api
     */
    public function canReceive(User $user): bool
    {
        return true;
    }

    /**
     * @internal
     */
    final public function getGroupingService(): GroupingService
    {
        return $this->_groupingService ??= new GroupingService($this);
    }

    /**
     * The built-in parameters and those of {@see getMessageParams()}, rendered for a channel.
     *
     * @param int $previewLength max length of the content preview
     * @param bool $strongContent whether the `content` parameter is wrapped in `<strong>` (HTML mails)
     */
    private function renderParams(MessageFormat $format, int $previewLength, bool $strongContent = false): array
    {
        $params = array_merge(
            $this->getBuiltInParams($previewLength, $strongContent),
            $this->getMessageParams(),
        );

        return MessageParam::renderAll($params, $format);
    }

    /**
     * @return array<string, MessageParam|string|int>
     */
    private function getBuiltInParams(int $previewLength, bool $strongContent): array
    {
        [$namesHtml, $namedCount] = $this->resolveDisplayNames(
            static fn(User $user): string => MessageParam::user($user)->render(MessageFormat::Html),
        );
        [$namesText] = $this->resolveDisplayNames(
            static fn(User $user): string => MessageParam::user($user)->render(MessageFormat::Text),
        );

        return array_merge([
            'displayName' => $this->originator !== null ? MessageParam::user($this->originator) : '',
            'displayNames' => MessageParam::html($namesHtml, $namesText),
            'namedCount' => $namedCount,
            'groupCount' => $this->groupCount,
        ], $this->getContentParams($previewLength, $strongContent));
    }

    /**
     * `content` and `contentTitle` of {@see getSubjectRecord()}; `[Deleted]` for both when the
     * notification is about a content whose record is gone; `[]` when it is about no content.
     *
     * @return array<string, MessageParam|string>
     */
    private function getContentParams(int $maxLength, bool $strongContent): array
    {
        $owner = $this->getSubjectRecord();
        if ($owner === null) {
            if ($this->record->content_id === null) {
                return [];
            }

            $deleted = Yii::t('NotificationModule.base', '[Deleted]');

            return ['content' => $deleted, 'contentTitle' => $deleted];
        }

        // getContentInfo() returns HTML; its plain text twin is the decoded HTML
        $content = ContentHelper::getContentInfo($owner, true, $maxLength);
        $contentTitle = ContentHelper::getContentInfo($owner, false, $maxLength);
        $decode = static fn(string $html): string => html_entity_decode($html, ENT_QUOTES | ENT_HTML5);

        return [
            'content' => MessageParam::html($strongContent ? Html::strong($content) : $content, $decode($content)),
            'contentTitle' => MessageParam::html($contentTitle, $decode($contentTitle)),
        ];
    }

    /**
     * The originator and the other members of a group, e.g. "Anna, Ben and 2 more"; `''` below two
     * members. Without an originator, the others only. With the number of people it names (0, 1 or 2).
     *
     * @param callable(User): string $formatter
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
            $names[] = $formatter($this->originator);
            $total++;
        }
        foreach ($grouping->getOtherGroupedUsers() as $user) {
            $names[] = $formatter($user);
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
