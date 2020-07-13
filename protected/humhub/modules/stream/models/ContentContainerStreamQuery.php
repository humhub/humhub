<?php


namespace humhub\modules\stream\models;


use humhub\modules\content\components\ContentContainerActiveRecord;
use humhub\modules\stream\models\filters\ContentContainerStreamFilter;
use humhub\modules\stream\models\filters\PinnedContentStreamFilter;

/**
 * This query class adds support for pinned container related streams.
 *
 * @package modules\stream\models
 */
class ContentContainerStreamQuery extends WallStreamQuery
{
    /**
     * @var ContentContainerActiveRecord
     */
    public $container;

    /**
     * @inheritdoc
     * @throws \yii\base\InvalidConfigException
     */
    protected function beforeApplyFilters()
    {
        $this->addFilterHandler(new ContentContainerStreamFilter(['container' => $this->container]));

        if($this->channel !== static::CHANNEL_ACTIVITY) {
            $this->addFilterHandler(new PinnedContentStreamFilter(['container' => $this->container]));
        }
        parent::beforeApplyFilters();
    }

}
