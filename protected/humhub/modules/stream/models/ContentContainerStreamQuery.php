<?php


namespace humhub\modules\stream\models;


use humhub\modules\content\components\ContentContainerActiveRecord;
use humhub\modules\stream\models\filters\ContentContainerStreamFilter;
use humhub\modules\stream\models\filters\PinnedContentStreamFilter;
use yii\base\InvalidConfigException;

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
     * @var bool whether or not to sort by pinned content
     */
    public $pinnedContentSupport = true;

    /**
     * @var PinnedContentStreamFilter
     */
    private $pinnedContentStreamFilter;

    /**
     * @inheritdoc
     * @throws InvalidConfigException
     */
    protected function beforeApplyFilters()
    {
        parent::beforeApplyFilters();

        $this->addFilterHandler(new ContentContainerStreamFilter(['container' => $this->container]));

        $this->pinnedContentStreamFilter = new PinnedContentStreamFilter(['container' => $this->container]);

        if($this->pinnedContentSupport) {
            $this->addFilterHandler($this->pinnedContentStreamFilter);
        }

    }

    public function all()
    {
        $result = parent::all();

        if(!empty($this->pinnedContentStreamFilter->pinnedContent)) {
            $result = array_merge($this->pinnedContentStreamFilter->pinnedContent, $result);
        }

        return $result;
    }

}
