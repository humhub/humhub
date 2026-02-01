<?php

namespace humhub\modules\content\activities;

use humhub\modules\activity\components\ActiveQueryActivity;
use humhub\modules\activity\models\Activity;
use Yii;
use humhub\modules\activity\components\BaseContentActivity;
use humhub\modules\activity\interfaces\ConfigurableActivityInterface;

final class ContentCreatedActivity extends BaseContentActivity implements ConfigurableActivityInterface
{
    /**
     * @var array Content type classes which should be not grouped
     */
    public array $contentTypeNoGrouping = [];

    public static function getTitle(): string
    {
        return Yii::t('ContentModule.activities', 'Contents');
    }

    public static function getDescription(): string
    {
        return Yii::t('ContentModule.activities', 'Whenever a new content (e.g. post) has been created.');
    }

    protected function getMessage(array $params): string
    {
        if ($this->groupCount > 1) {
            $params['groupCount'] = $this->groupCount - 1;
            return Yii::t(
                'ContentModule.activities',
                '{displayName} created a new {content} and {groupCount} more.',
                $params,
            );
        } else {
            return Yii::t(
                'ContentModule.activities',
                '{displayName} created a new {content}.',
                $params,
            );
        }
    }

    public function getUrl(bool $scheme = false): ?string
    {
        //ToDo: If grouped, link to Stream with enabled filters
        return parent::getUrl($scheme);
    }

    public function findGroupedQuery(): ?ActiveQueryActivity
    {
        return Activity::find()
            ->andWhere(['activity.class' => static::class])
            ->andWhere(['activity.contentcontainer_id' => $this->contentContainer->id])
            ->andWhere(['activity.created_by' => $this->user->id])
            ->andWhere(['content.object_model' => $this->record->content->object_model])
            ->andWhere(['NOT IN', 'content.object_model', $this->contentTypeNoGrouping]);
    }
}
