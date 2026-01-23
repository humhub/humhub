<?php

namespace humhub\modules\activity\components;

use humhub\modules\activity\models\Activity;
use humhub\modules\activity\models\Activity as ActivityRecord;
use humhub\modules\content\models\ContentContainer;
use humhub\modules\user\models\User;
use yii\base\BaseObject;

abstract class BaseActivity extends BaseObject
{
    public readonly ActivityRecord $record;

    protected readonly ContentContainer $contentContainer;

    protected readonly User $user;

    protected readonly string $createdAt;
    public ?int $groupingThreshold = null;
    public int $groupingTimeBucketSeconds = 900;
    public int $groupCount;

    public function __construct(ActivityRecord $record, $config = [])
    {
        parent::__construct($config);

        $this->contentContainer = $record->contentContainer;
        $this->user = $record->createdBy;
        $this->createdAt = $record->created_at;
        $this->record = $record;
        $this->groupCount = $this->record->groupCount;
    }

    abstract public function asText(): string;

    public function getViewParams(): array
    {
        return [
            'url' => $this->getUrl(),
            'contentContainer' => $this->contentContainer,
            'createdAt' => $this->createdAt,
            'user' => $this->user,
        ];
    }

    public function getUrl(bool $scheme = true): ?string
    {
        return $this->contentContainer->polymorphicRelation->getUrl($scheme);
    }

    public function findGroupedQuery(): ActiveQueryActivity
    {
        return Activity::find()
            ->timeBucket($this->groupingTimeBucketSeconds, $this->createdAt)
            ->andWhere(['activity.class' => static::class])
            ->andWhere(['activity.contentcontainer_id' => $this->contentContainer->id])
            ->andWhere(['activity.created_by' => $this->user->id]);
    }
}
