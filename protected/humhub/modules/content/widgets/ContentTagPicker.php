<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

namespace humhub\modules\content\widgets;

use humhub\modules\content\components\ContentContainerActiveRecord;
use humhub\modules\content\components\ContentTagActiveQuery;
use humhub\modules\content\models\ContentTag;
use humhub\modules\ui\form\widgets\BasePicker;
use Yii;
use yii\db\ActiveRecord;

/**
 * This InputWidget provides a generic ContentTag Dropdown
 *
 *
 * @package humhub\modules\content\widgets
 */
class ContentTagPicker extends BasePicker
{
    /**
     * @var string tagClass
     */
    public $itemClass = ContentTag::class;

    /**
     * @var string tagClass
     */
    public $limit = 50;

    public $showDefaults = false;

    /**
     * @var ContentContainerActiveRecord container can be used to create urls etc
     */
    public $contentContainer;

    public function init()
    {
        parent::init();
        if ($this->showDefaults) {
            $this->defaultResults = $this->findDefaults();
        }
    }

    protected function findDefaults()
    {
        /* @var ContentTagActiveQuery $query */
        $query = call_user_func([$this->itemClass, 'findByContainer'], $this->contentContainer, true);
        $query->readable()->limit($this->limit);

        return Yii::$app->runtimeCache->getOrSet(__METHOD__ . $this->id, function () use ($query) {
            return $query->all();
        });
    }

    public static function search($term, $contentContainer = null, $includeGlobal = false)
    {
        $instance = new static();

        /* @var ContentTagActiveQuery $query */
        $query = call_user_func([$instance->itemClass, 'find']);
        if (!empty($term)) {
            $query->andWhere(['like', 'content_tag.name', $term]);
        }
        $query->readable();

        return static::jsonResult($query->limit($instance->limit)->all());
    }

    public static function searchByContainer($term, $contentContainer, $includeGlobal = true)
    {
        if (!$contentContainer) {
            return static::search($term);
        }

        $instance = new static();

        /* @var ContentTagActiveQuery $query */
        $query = call_user_func([$instance->itemClass, 'findByContainer'], $contentContainer, $includeGlobal);
        if (!empty($term)) {
            $query->andWhere(['like', 'content_tag.name', $term]);
        }
        $query->readable();

        return static::jsonResult($query->limit($instance->limit)->all());
    }

    public static function jsonResult($tags)
    {
        $result = [];
        foreach ($tags as $tag) {
            $result[] = [
                'id' => $tag->id,
                'text' => $tag->name,
                'image' => $tag->color,
            ];
        }

        return $result;
    }

    /**
     * Used to retrieve the option text of a given $item.
     *
     * @param ActiveRecord $item selected item
     * @return string item option text
     */
    protected function getItemText($item)
    {
        if (!$item instanceof ContentTag) {
            return;
        }

        return $item->name;
    }

    /**
     * Used to retrieve the option image url of a given $item.
     *
     * @param ActiveRecord $item selected item
     * @return string|null image url or null if no selection image required.
     */
    protected function getItemImage($item)
    {
        return null;
    }
}
