<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

namespace humhub\modules\content\widgets;

use humhub\modules\content\components\ContentContainerActiveRecord;
use Yii;
use humhub\widgets\BasePickerField;
use humhub\modules\content\models\ContentTag;

/**
 * This InputWidget provides a generic ContentTag Dropdown
 *
 *
 * @package humhub\modules\content\widgets
 */
class ContentTagPicker extends BasePickerField
{
    /**
     * @var string tagClass
     */
    public $itemClass = ContentTag::class;

    /**
     * @var string tagClass
     */
    public $limit = 20;

    /**
     * @var ContentContainerActiveRecord container can be used to create urls etc
     */
    public $contentContainer;

    public function int() {
        if(!$this->tagClass) {
            $this->tagClass = ContentTag::class;
            // Reset default behavior inf no specific tagClass is given
            if($this->type === true) {
                $this->type = null;
            }
        }
    }

    public static function search($term)
    {
        $instance = new static();
        $query = call_user_func([$instance->itemClass, 'find']);
        $query->andWhere(['like','content_tag.name', $term])->limit($instance->limit);
        return static::jsonResult($query->all());
    }

    public static function searchByContainer($term, $contentContainer, $includeGlobal = true)
    {
        if(!$contentContainer) {
            return static::search($term);
        }

        $instance = new static();
        $query = call_user_func([$instance->itemClass, 'findByContainer'], $contentContainer, $includeGlobal);
        $query->andWhere(['like','content_tag.name', $term])->limit($instance->limit);

        return static::jsonResult($query->all());
    }

    public static function jsonResult($topics)
    {
        $result = [];
        foreach($topics as $topic) {
            $result[] = [
                'id' => $topic->id,
                'text' => $topic->name
            ];
        }

        return $result;
    }


    /**
     * Used to retrieve the option text of a given $item.
     *
     * @param \yii\db\ActiveRecord $item selected item
     * @return string item option text
     */
    protected function getItemText($item)
    {
        if(!$item instanceof ContentTag) {
            return;
        }

        return $item->name;
    }

    /**
     * Used to retrieve the option image url of a given $item.
     *
     * @param \yii\db\ActiveRecord $item selected item
     * @return string|null image url or null if no selection image required.
     */
    protected function getItemImage($item)
    {
        return null;
    }
}