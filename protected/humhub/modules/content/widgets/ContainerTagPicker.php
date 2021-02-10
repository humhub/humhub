<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2021 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\content\widgets;

use humhub\modules\space\models\Space;
use humhub\modules\ui\form\widgets\BasePicker;
use humhub\modules\user\models\forms\AccountSettings;
use humhub\modules\user\models\User;
use Yii;
use yii\helpers\Url;

/**
 * This InputWidget provides a Container Tags Picker
 *
 * @package humhub\modules\content\widgets
 */
class ContainerTagPicker extends BasePicker
{
    /**
     * @inheritdoc
     */
    public $itemKey = 'name';

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        if ($this->model instanceof Space) {
            $this->url = Url::to(['/space/browse/search-tags-json']);
        } else if($this->model instanceof AccountSettings) {
            $this->url = Url::to(['/user/account/search-tags-json']);
        }
    }

    /**
     * @inheritdoc
     */
    protected function getData()
    {
        return array_merge(parent::getData(), [
            'placeholder-more' => Yii::t('ContentModule.widgets_ContainerTagPicker', 'Add tag...'),
            'no-result' => Yii::t('ContentModule.widgets_ContainerTagPicker', 'No tags found for the given query'),
        ]);
    }

    public function loadItems($selection = null)
    {
        if ($selection === '') {
            return [];
        }

        $tags = explode(',', $selection);
        foreach ($tags as $t => $tag) {
            $tags[$t] = (object)['name' => trim($tag)];
        }

        return $tags;
    }

    /**
     * @inheritdoc
     */
    protected function getItemText($item)
    {
        return $item->name;
    }

    /**
     * @inheritdoc
     */
    protected function getItemImage($item)
    {
        return null;
    }

    /**
     * Search tags data from Space/User containers for JSON response
     *
     * @param string $contentContainerClass
     * @param string $keyword
     * @return array
     */
    public static function searchTagsFromContainers($contentContainerClass, $keyword)
    {
        $tags = [];
        $keyword = trim($keyword);

        if ($keyword === '') {
            return $tags;
        }

        $tags[$keyword] = $keyword;

        /* @var $contentContainerClass Space|User */
        $contentContainers = $contentContainerClass::find()
            ->visible()
            ->search($keyword, ['tags'])
            ->all();

        /* @var $contentContainer Space|User */
        foreach ($contentContainers as $contentContainer) {
            $containerTags = explode(',', $contentContainer->tags);
            foreach ($containerTags as $containerTag) {
                $containerTag = trim($containerTag);
                $uniqueTag = strtolower($containerTag);
                if (!isset($tags[$uniqueTag]) && stripos($containerTag, $keyword) !== false) {
                    $tags[$uniqueTag] = $containerTag;
                }
            }
        }

        foreach ($tags as $t => $tag) {
            $tags[] = ['id' => $tag, 'text' => $tag];
            unset($tags[$t]);
        }

        return $tags;
    }
}
