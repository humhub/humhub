<?php


namespace modules\stream\models;


use humhub\modules\content\components\ContentContainerActiveRecord;
use humhub\modules\stream\models\filters\ContentContainerStreamFilter;
use humhub\modules\stream\models\filters\PinnedContentStreamFilter;
use humhub\modules\stream\models\WallStreamQuery;

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
        $this->addFilterHandler(new PinnedContentStreamFilter(['container' => $this->container]));
        parent::beforeApplyFilters();
    }

}
