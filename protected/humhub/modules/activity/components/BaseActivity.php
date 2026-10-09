<?php

namespace humhub\modules\activity\components;

use humhub\components\message\MessageFormat;
use humhub\components\message\MessageParam;
use humhub\helpers\Html;
use humhub\modules\activity\models\Activity as ActivityRecord;
use humhub\modules\activity\services\GroupingService;
use humhub\modules\content\models\ContentContainer;
use humhub\modules\user\models\User;
use Yii;
use yii\base\BaseObject;

/**
 * Base class of an activity: its sentence ({@see getMessage()}), the {@see getMessageParams()} the
 * sentence needs beyond the built-in ones, the {@see getUrl()} it links to and the
 * {@see getGroupingQuery()} that finds its siblings. See `docs/develop/concept-activities.md`.
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
     * @var int minimum members in a group
     * @api
     */
    public int $groupingThreshold = 2;

    /**
     * @var int the time bucket the siblings of {@see getGroupingQuery()} must fall into
     * @api
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
     * The URL the entry links to: the content container by default.
     *
     * @api
     */
    public function getUrl(bool $scheme = true): ?string
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
        return [
            'displayName' => MessageParam::user($this->user),
            'displayNames' => MessageParam::html(
                $this->formatDisplayNames(static fn($dn) => Html::strong(Html::encode($dn))),
                $this->formatDisplayNames(static fn($dn) => $dn),
            ),
            'groupCount' => $this->groupCount,
        ];
    }

    /**
     * Whether the message parameters are being rendered for a mail (rather than the activity box),
     * e.g. to pick the length of a preview.
     *
     * @internal read by the core's base activities, see {@see BaseContentActivity::getPreviewLength()}
     * @since 1.20
     */
    protected function isRenderingMail(): bool
    {
        return $this->_renderingMail;
    }

    /**
     * Finds the activities this one is grouped with; `null` (the default) for no grouping.
     *
     * @api
     */
    public function getGroupingQuery(): ?ActiveQueryActivity
    {
        return null;
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
     * The built-in parameters and those of {@see getMessageParams()}, rendered for an output.
     *
     * @return array<string, string|int>
     */
    private function renderMessageParams(MessageFormat $format, bool $mail): array
    {
        $previous = $this->_renderingMail;
        $this->_renderingMail = $mail;

        try {
            $params = array_merge($this->getBuiltInMessageParams(), $this->getMessageParams());
        } finally {
            $this->_renderingMail = $previous;
        }

        return MessageParam::renderAll($params, $format);
    }
}
