<?php

namespace humhub\modules\content\activities;

use humhub\modules\activity\components\BaseContentActivity;
use humhub\modules\activity\interfaces\ConfigurableActivityInterface;
use humhub\modules\activity\models\Activity;
use humhub\modules\notification\components\Grouping;
use Yii;

final class ContentCreatedActivity extends BaseContentActivity implements ConfigurableActivityInterface
{
    /**
     * @inheritdoc
     * @deprecated since 1.20, the threshold of {@see grouping()} (4) applies
     */
    public int $groupingThreshold = 4;

    /**
     * @var array Content type classes which should be not grouped
     */
    public array $contentTypeNoGrouping = [];

    /**
     * The contents of one type and visibility created by one user in one container.
     *
     * @inheritdoc
     */
    public static function grouping(): ?Grouping
    {
        return Grouping::byClass()
            ->andContainer()
            ->andOriginator()
            ->andContentType()
            ->andContentVisibility()
            ->withThreshold(4);
    }

    /**
     * Not for the content types of {@see $contentTypeNoGrouping}.
     *
     * @inheritdoc
     * @internal
     */
    public function isGroupable(): bool
    {
        return !in_array($this->content->object_model, $this->contentTypeNoGrouping, true);
    }

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

    /**
     * @inheritdoc
     */
    protected function getRedactedMessage(array $params): ?string
    {
        return Yii::t('ContentModule.activities', '{groupCount, plural, =1{{displayName} created a new {contentType}.} other{{displayName} created # new entries.}}', $params);
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
}
