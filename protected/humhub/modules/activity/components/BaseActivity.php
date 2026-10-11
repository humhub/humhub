<?php

namespace humhub\modules\activity\components;

use humhub\helpers\Html;
use humhub\modules\activity\interfaces\ConfigurableActivityInterface;
use humhub\modules\activity\models\Activity as ActivityRecord;
use humhub\modules\activity\services\ActivityManager;
use humhub\modules\activity\services\GroupingService;
use humhub\modules\content\components\ContentContainerActiveRecord;
use humhub\modules\content\interfaces\ContentProvider;
use humhub\modules\content\models\ContentContainer;
use humhub\modules\notification\components\Grouping;
use humhub\modules\notification\components\MessageFormat;
use humhub\modules\notification\components\MessageParam;
use humhub\modules\user\models\User;
use Yii;
use yii\base\BaseObject;
use yii\base\InvalidCallException;

/**
 * Base class of an activity: its sentence ({@see getMessage()}), the {@see getMessageParams()} the
 * sentence needs beyond the built-in ones, the {@see getUrl()} it links to and the
 * {@see grouping()} that decides which activities are shown as one entry, and the
 * {@see getRedactedMessage()} the summary mail shows when the network hides content. It is
 * written with {@see send()}. See `docs/develop/concept-activities.md`.
 *
 * The built-in message parameters, rendered per output together with those of {@see getMessageParams()}:
 *
 * - `displayName` - the user who caused the activity
 * - `displayNames` - the users of a grouped activity, e.g. "Anna, Bob and 2 more" (`''` when ungrouped)
 * - `groupCount` - how many activities the entry stands for (1 when ungrouped)
 *
 * @property-read User[] $groupedUsers
 * @api
 */
abstract class BaseActivity extends BaseObject
{
    /**
     * @api
     */
    public readonly ActivityRecord $record;

    /**
     * @api
     */
    public readonly ?ContentContainer $contentContainer;

    /**
     * @var User the user who caused the activity
     * @api
     */
    public readonly User $user;

    /**
     * @api
     */
    public readonly string $createdAt;

    /**
     * @var int how many activities the entry stands for (1 when ungrouped)
     * @api
     */
    public readonly int $groupCount;

    /**
     * @var int minimum members in a group of the deprecated {@see getGroupingQuery()}
     * @api
     * @deprecated since 1.20, use {@see Grouping::withThreshold()} in {@see grouping()}; only used
     * with an override of {@see getGroupingQuery()}
     */
    public int $groupingThreshold = 2;

    /**
     * @var int the time bucket the siblings of the deprecated {@see getGroupingQuery()} must fall into
     * @api
     * @deprecated since 1.20, use {@see Grouping::withTimeBucket()} in {@see grouping()}; only used
     * with an override of {@see getGroupingQuery()}
     */
    public int $groupingTimeBucketSeconds = 900;

    private ?GroupingService $_groupingService = null;

    /**
     * Whether the parameters being rendered are those of a mail, see {@see isRenderingMail()}.
     */
    private bool $_renderingMail = false;

    /**
     * Activities are created by the core from their record, see `ActivityManager::load()`.
     *
     * @api to be extended, e.g. to resolve and check a related record
     */
    public function __construct(ActivityRecord $record, $config = [])
    {
        parent::__construct($config);

        $this->contentContainer = $record->contentContainer;
        $this->user = $record->createdBy;
        $this->createdAt = $record->created_at;
        $this->record = $record;
        $this->groupCount = $this->record->group_count ?? 1;
    }

    /**
     * Writes an activity of this class about `$target` caused by `$user` (the logged-in user by
     * default), groups it and tells the container's audience; `null` when a handler of
     * {@see ActivityManager::EVENT_BEFORE_DISPATCH} vetoed it.
     *
     * ```php
     * TaskCreatedActivity::send($task, $user);
     * ```
     *
     * @param ContentProvider|ContentContainerActiveRecord $target what the activity is about: a
     * content record (e.g. a post), a content addon (e.g. a comment, stored with its content) or a
     * container (a space or a user)
     * @param User|null $user the user who caused the activity, the logged-in user by default
     * @api
     * @since 1.20
     */
    final public static function send(ContentProvider|ContentContainerActiveRecord $target, ?User $user = null): ?static
    {
        if ((new \ReflectionClass(static::class))->isAbstract()) {
            throw new InvalidCallException('An activity is sent by its concrete class, not by ' . static::class . '.');
        }

        /** @var static|null $activity */
        $activity = ActivityManager::dispatch(static::class, $target, $user);

        return $activity;
    }

    /**
     * Which activities of this class are shown as one entry, e.g. `Grouping::byContainer()`;
     * `null` (the default) for none. Ignored when the class (or a subclass) overrides the deprecated
     * {@see getGroupingQuery()}: that query wins, as up to 1.19. Activities do not support
     * {@see Grouping::unseenOnly()}.
     *
     * @api
     * @since 1.20
     */
    public static function grouping(): ?Grouping
    {
        return null;
    }

    /**
     * The sentence of the activity, e.g.
     * `Yii::t('TasksModule.base', '{displayName} created the task {contentTitle}.', $params)`.
     *
     * @param array<string, string|int> $params the built-in parameters and those of
     * {@see getMessageParams()}, rendered for the output: HTML-encoded for the web and HTML mails,
     * plain text for text mails
     * @api
     */
    abstract protected function getMessage(array $params): string;

    /**
     * The message parameters this class adds to the built-in ones (and may override them with):
     * a plain string or a {@see MessageParam}, e.g. `['task' => MessageParam::emphasis($task->title)]`.
     * Values are rendered per output, see {@see getMessage()}.
     *
     * @return array<string, MessageParam|string|int>
     * @api
     * @since 1.20
     */
    protected function getMessageParams(): array
    {
        return [];
    }

    /**
     * The sentence without content that the summary mail shows under the notification privacy
     * mode "Hide content" ({@see \humhub\modules\notification\components\NotificationPrivacy::Redacted});
     * `null` (the default) for the core's form "{displayName}: {title}" - the title of a
     * {@see ConfigurableActivityInterface}, else "New activity".
     *
     * A whole sentence, so translators can build it in their language, e.g.
     * `Yii::t('TasksModule.base', '{displayName} created a task.', $params)`. A class whose
     * {@see getMessage()} holds no content may return `$this->getMessage($params)`.
     *
     * @param array<string, string|int> $params only values that carry no content, rendered for
     * the output: `displayName`, `displayNames`, `groupCount`, `contentType` (the type of the
     * activity's content, e.g. "post"; only for an activity about a content) and the parameters of
     * {@see getMessageParams()} that are a {@see MessageParam::user()} - never `content`,
     * `contentTitle` or other parameters
     * @api
     * @since 1.20
     */
    protected function getRedactedMessage(array $params): ?string
    {
        return null;
    }

    /**
     * The plain text sentence of the summary mail.
     *
     * @api
     */
    final public function asMailText(): string
    {
        return $this->getMessage($this->getMessageParamsMailText());
    }

    /**
     * The HTML sentence of the activity box.
     *
     * @api
     */
    final public function asWeb(): string
    {
        return $this->getMessage($this->getMessageParamsWeb());
    }

    /**
     * The HTML sentence of the summary mail.
     *
     * @api
     */
    final public function asMailHtml(): string
    {
        return $this->getMessage($this->getMessageParamsMailHtml());
    }

    /**
     * The HTML sentence without content of the summary mail, see {@see getRedactedMessage()}.
     *
     * @internal for the summary mail
     * @since 1.20
     */
    final public function asRedactedHtml(): string
    {
        return $this->renderRedacted(MessageFormat::Html);
    }

    /**
     * The plain text sentence without content of the summary mail, see {@see getRedactedMessage()}.
     *
     * @internal for the summary mail
     * @since 1.20
     */
    final public function asRedactedText(): string
    {
        return $this->renderRedacted(MessageFormat::Text);
    }

    /**
     * The URL the entry links to: the content container by default. Relative unless `$scheme`
     * (the summary mail asks for an absolute one).
     *
     * @api
     */
    public function getUrl(bool $scheme = false): ?string
    {
        return $this->contentContainer?->polymorphicRelation?->getUrl($scheme);
    }

    /**
     * The message parameters of the plain text mail.
     *
     * @deprecated since 1.20, use {@see getMessageParams()}
     * @internal still called (and its result used) when a subclass overrides it
     */
    protected function getMessageParamsMailText(): array
    {
        return $this->renderMessageParams(MessageFormat::Text, true);
    }

    /**
     * The message parameters of the activity box.
     *
     * @deprecated since 1.20, use {@see getMessageParams()}
     * @internal still called (and its result used) when a subclass overrides it
     */
    protected function getMessageParamsWeb(): array
    {
        // Parameters a subclass adds by overriding getMessageParamsMailText() alone reach the web as well, as up to 1.19
        return array_merge(
            $this->getMessageParamsMailText(),
            $this->renderMessageParams(MessageFormat::Html, false),
        );
    }

    /**
     * The message parameters of the HTML mail.
     *
     * @deprecated since 1.20, use {@see getMessageParams()}
     * @internal still called (and its result used) when a subclass overrides it
     */
    protected function getMessageParamsMailHtml(): array
    {
        // Based on the web parameters, as up to 1.19, so an override of getMessageParamsWeb() reaches the
        // mail too; only parameters rendered differently for a mail (e.g. a longer preview) replace them.
        $mail = $this->renderMessageParams(MessageFormat::Html, true);
        $web = $this->renderMessageParams(MessageFormat::Html, false);

        return array_merge($this->getMessageParamsWeb(), array_diff_assoc($mail, $web));
    }

    /**
     * The `displayNames` phrase of a grouped activity, e.g. "Anna, Bob and 2 more", with each name
     * formatted by `$formatter`; `''` when ungrouped.
     *
     * @param callable(string): string $formatter
     * @deprecated since 1.20, the built-in `displayNames` parameter is rendered per output; override
     * {@see getMessageParams()} to replace it
     * @internal
     */
    protected function formatDisplayNames(callable $formatter): string
    {
        if ($this->groupCount < 2) {
            return '';
        }

        $otherUsers = $this->getGroupingService()->getOtherGroupedUsers(Yii::$app->user?->getIdentity());

        if (count($otherUsers) === 0) {
            // A group of one person's own activities, or one whose only other participant is
            // the reader themselves: the phrase is that person, and it must not come back
            // empty - a grouped message renders `{displayNames}` and nothing else.
            return $formatter($this->user->displayName);
        }

        if (count($otherUsers) === 1) {
            return Yii::t(
                'ActivityModule.base',
                '{displayName1} and {displayName2}',
                [
                    'displayName1' => $formatter($this->user->displayName),
                    'displayName2' => $formatter($otherUsers[0]->displayName),
                ],
            );
        } elseif (count($otherUsers) > 1) {
            return Yii::t(
                'ActivityModule.base',
                '{displayName1}, {displayName2} and {count} more',
                [
                    'displayName1' => $formatter($this->user->displayName),
                    'displayName2' => $formatter($otherUsers[0]->displayName),
                    'count' => count($otherUsers) - 1,
                ],
            );
        }

        return '';
    }

    /**
     * The built-in message parameters, before those of {@see getMessageParams()}.
     *
     * @return array<string, MessageParam|string|int>
     * @internal extended by the core's base activities, e.g. {@see BaseContentActivity}
     * @since 1.20
     */
    protected function getBuiltInMessageParams(): array
    {
        return $this->getNameParams();
    }

    /**
     * The max length of a content preview in the output being rendered, e.g. of a
     * {@see MessageParam::content()}: 300 characters for mails, 60 for the activity box.
     *
     * @api
     * @since 1.20
     */
    protected function getPreviewLength(): int
    {
        return $this->isRenderingMail() ? 300 : MessageParam::CONTENT_LENGTH;
    }

    /**
     * Whether the message parameters are being rendered for a mail (rather than the activity box),
     * e.g. to pick the length of a preview.
     *
     * @internal read by {@see getPreviewLength()} and the core's activities
     * @since 1.20
     */
    protected function isRenderingMail(): bool
    {
        return $this->_renderingMail;
    }

    /**
     * Finds the activities this one is grouped with; `null` (the default) for no grouping. An
     * override wins over {@see grouping()}; the core narrows it to the time bucket of
     * {@see $groupingTimeBucketSeconds} and groups from {@see $groupingThreshold} matches on.
     *
     * @api
     * @deprecated since 1.20, declare {@see grouping()} instead; an override keeps working
     */
    public function getGroupingQuery(): ?ActiveQueryActivity
    {
        return null;
    }

    /**
     * Whether this activity takes part in the {@see grouping()} of its class - `false` leaves it
     * ungrouped, e.g. for a configuration of the instance.
     *
     * @internal for the core's activities, see `ContentCreatedActivity::$contentTypeNoGrouping`
     * @since 1.20
     */
    public function isGroupable(): bool
    {
        return true;
    }

    /**
     * @internal
     */
    public function getGroupingService(): GroupingService
    {
        if ($this->_groupingService === null) {
            $this->_groupingService = new GroupingService($this);
        }

        return $this->_groupingService;
    }

    /**
     * {@see getRedactedMessage()}, else {@see getMessage()} when it names no content (see
     * {@see MessageParam::renderRedacted()}), else "{displayName}: {title}".
     */
    private function renderRedacted(MessageFormat $format): string
    {
        $params = $this->getNameParams();
        $contentType = $this->record->content?->getPolymorphicRelation()?->getContentName();
        if ($contentType !== null) {
            $params['contentType'] = MessageParam::text((string)$contentType);
        }
        foreach ($this->getMessageParams() as $name => $value) {
            if ($value instanceof MessageParam && $value->isUser()) {
                $params[$name] = $value;
            }
        }
        $params = MessageParam::renderAll($params, $format);

        $message = $this->getRedactedMessage($params) ?? $this->renderRedactedMessage($format);
        if ($message !== null) {
            return $message;
        }

        $title = $this instanceof ConfigurableActivityInterface
            ? static::getTitle()
            : Yii::t('ActivityModule.base', 'New activity');

        return Yii::t('ActivityModule.base', '{displayName}: {title}', [
            'displayName' => $params['displayNames'] !== '' ? $params['displayNames'] : $params['displayName'],
            'title' => MessageParam::text($title)->render($format),
        ]);
    }

    /**
     * {@see getMessage()} when it names no content - not for a class that still builds its
     * parameters through the deprecated `getMessageParams*()` methods, which may hold content of
     * their own.
     */
    private function renderRedactedMessage(MessageFormat $format): ?string
    {
        foreach (['getMessageParamsMailText', 'getMessageParamsWeb', 'getMessageParamsMailHtml'] as $method) {
            if ((new \ReflectionMethod($this, $method))->getDeclaringClass()->getName() !== self::class) {
                return null;
            }
        }

        return MessageParam::renderRedacted(
            fn(array $params): string => $this->getMessage($params),
            array_merge($this->getBuiltInMessageParams(), $this->getMessageParams()),
            $format,
        );
    }

    /**
     * `displayName`, `displayNames` and `groupCount` - the built-in parameters of this class
     * without those a subclass adds (e.g. the content).
     *
     * @return array<string, MessageParam|string|int>
     */
    private function getNameParams(): array
    {
        return [
            'displayName' => MessageParam::user($this->user),
            'displayNames' => MessageParam::html(
                $this->formatDisplayNames(static fn($dn) => Html::strong(Html::encode($dn))),
                $this->formatDisplayNames(static fn($dn) => $dn),
                redactable: true,
            ),
            'groupCount' => $this->groupCount,
        ];
    }

    /**
     * The built-in parameters and those of {@see getMessageParams()}, rendered for an output.
     *
     * @return array<string, string|int>
     */
    private function renderMessageParams(MessageFormat $format, bool $mail): array
    {
        $previous = $this->_renderingMail;
        $this->_renderingMail = $mail;

        try {
            return MessageParam::renderAll(
                array_merge($this->getBuiltInMessageParams(), $this->getMessageParams()),
                $format,
                $this->getPreviewLength(),
                $mail && $format === MessageFormat::Html,
            );
        } finally {
            $this->_renderingMail = $previous;
        }
    }
}
