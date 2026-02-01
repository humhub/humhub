<?php

namespace humhub\modules\activity\components;

use humhub\helpers\Html;
use humhub\modules\activity\models\Activity as ActivityRecord;
use humhub\modules\activity\services\GroupingService;
use humhub\modules\content\models\ContentContainer;
use humhub\modules\user\models\User;
use Yii;
use yii\base\BaseObject;

/**
 * @property-read User[] $groupedUsers
 */
abstract class BaseActivity extends BaseObject
{
    public readonly ActivityRecord $record;

    public readonly ContentContainer $contentContainer;

    public readonly User $user;

    public readonly string $createdAt;
    public readonly int $groupCount;
    public int $groupingThreshold = 4;
    public int $groupingTimeBucketSeconds = 900;

    protected GroupingService $groupingService;

    public function __construct(ActivityRecord $record, $config = [])
    {
        parent::__construct($config);

        $this->contentContainer = $record->contentContainer;
        $this->user = $record->createdBy;
        $this->createdAt = $record->created_at;
        $this->record = $record;
        $this->groupCount = $this->record->group_count ?? 1;
        $this->groupingService = new GroupingService($this);
    }

    abstract protected function getMessage(array $params): string;

    final public function asText(): string
    {
        return $this->getMessage($this->getMessageParamsText());
    }

    final public function asHtml(): string
    {
        return $this->getMessage($this->getMessageParamsHtml());
    }

    final public function asHtmlMail(): string
    {
        return $this->getMessage($this->getMessageParamsHtmlMail());
    }

    public function getUrl(bool $scheme = true): ?string
    {
        return $this->contentContainer->polymorphicRelation->getUrl($scheme);
    }

    protected function getMessageParamsText(): array
    {
        return [
            'displayName' => $this->user->displayName,
            'displayNames' => $this->formatDisplayNames(fn($dn) => $dn),
            'groupCount' => $this->groupCount,
        ];
    }

    protected function getMessageParamsHtml(): array
    {
        return array_merge($this->getMessageParamsText(), [
            'displayName' => Html::strong(Html::encode($this->user->displayName)),
            'displayNames' => $this->formatDisplayNames(fn($dn) => Html::strong(Html::encode($dn))),
        ]);
    }

    protected function getMessageParamsHtmlMail(): array
    {
        return array_merge($this->getMessageParamsHtml(), [
            'displayName' => Html::strong(Html::encode($this->user->displayName)),
            'displayNames' => $this->formatDisplayNames(fn($dn) => Html::strong(Html::encode($dn))),
        ]);
    }

    protected function formatDisplayNames(callable $formatter): string
    {
        $groupedUsers = $this->groupingService->getGroupedUsers();

        if (count($groupedUsers) === 2) {
            return Yii::t(
                'ActivityModule.base',
                '{displayName1} and {displayName2}',
                [
                    'displayName1' => $formatter($groupedUsers[0]->displayName),
                    'displayName2' => $formatter($groupedUsers[1]->displayName),
                ],
            );
        } elseif (count($groupedUsers) > 2) {
            return Yii::t(
                'ActivityModule.base',
                '{displayName1}, {displayName2} and {count} more',
                [
                    'displayName1' => $formatter($groupedUsers[0]->displayName),
                    'displayName2' => $formatter($groupedUsers[2]->displayName),
                    'count' => count($groupedUsers) - 2,
                ],
            );
        }

        return '';
    }

    public function findGroupedQuery(): ?ActiveQueryActivity
    {
        return null;
    }
}
