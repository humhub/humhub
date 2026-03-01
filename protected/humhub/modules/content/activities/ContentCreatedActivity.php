<?php

namespace humhub\modules\content\activities;

use humhub\modules\activity\components\ActiveQueryActivity;
use humhub\modules\activity\components\BaseContentActivity;
use humhub\modules\activity\interfaces\ConfigurableActivityInterface;
use humhub\modules\activity\models\Activity;
use Yii;

final class ContentCreatedActivity extends BaseContentActivity implements ConfigurableActivityInterface
{
    public int $groupingThreshold = 4;

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
        if ($this->groupCount > 1) {
            $firstDate = Activity::find()->select('MIN(activity.created_at)')
                ->where(['activity.grouping_key' => $this->record->grouping_key]);
            $lastDate = Activity::find()->select('MAX(activity.created_at)')
                ->where(['activity.grouping_key' => $this->record->grouping_key]);

            return $this->content->container->createUrl(null, [
                'originators[]' => $this->user->guid,
                'includes[]' => $this->content->object_model,
                'date_filter_from' => Yii::$app->formatter->asDate($firstDate->scalar(), 'short'),
                'date_filter_to' => Yii::$app->formatter->asDate($lastDate->scalar(), 'short'),
            ], $scheme);
        }

        return parent::getUrl($scheme);
    }

    public function getGroupingQuery(): ?ActiveQueryActivity
    {
        return Activity::find()
            ->andWhere(['activity.class' => static::class])
            ->andWhere(['activity.contentcontainer_id' => $this->contentContainer->id])
            ->andWhere(['activity.created_by' => $this->user->id])
            ->andWhere(['content.object_model' => $this->record->content->object_model])
            ->andWhere(['content.visibility' => $this->record->content->visibility])
            ->andWhere(['NOT IN', 'content.object_model', $this->contentTypeNoGrouping]);
    }
}
