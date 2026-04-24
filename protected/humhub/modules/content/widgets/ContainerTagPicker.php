<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2021 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\content\widgets;

use humhub\modules\content\components\ContentContainerActiveRecord;
use humhub\modules\content\models\ContentContainerTag;
use humhub\modules\space\models\Space;
use humhub\modules\topic\models\Topic;
use humhub\modules\ui\form\widgets\BasePicker;
use humhub\modules\user\models\forms\AccountSettings;
use humhub\modules\user\models\User;
use Yii;
use yii\helpers\Url;

/**
 * This InputWidget provides a Container Tags Picker
 *
 * @package humhub\modules\content\widgets
 * @since 1.9
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
    public $itemClass = ContentContainerTag::class;

    /**
     * @inheritdoc
     */
    public $placeholderMore;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        $contentContainer = $this->getContentContainer();
        if ($contentContainer instanceof Space) {
            $this->url = Url::to(['/space/browse/search-tags-json', 'guid' => $contentContainer->guid]);
            ;
        } elseif ($contentContainer instanceof User) {
            $this->url = Url::to(['/user/account/search-tags-json', 'guid' => $contentContainer->guid]);
        }
    }

    /**
     * @inheritdoc
     */
    protected function getAttributes()
    {
        return array_merge(parent::getAttributes(), [
            'data-tags' => Topic::isAllowedToCreate($this->getContentContainer()) ? 'true' : 'false',
        ]);
    }

    /**
     * @inheritdoc
     */
    protected function getData()
    {
        return array_merge(parent::getData(), [
            'placeholder-more' => $this->placeholderMore ?? Yii::t('ContentModule.base', 'Add tag...'),
            'no-result' => Yii::t('ContentModule.base', 'No tags found for the given query'),
        ]);
    }

    public function loadItems($selection = null)
    {
        if (!is_array($selection)) {
            $selection = $this->model->getTags();
        }

        $tags = [];
        foreach ($selection as $tag) {
            $tags[] = (object)['name' => $tag];
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

    protected function getContentContainer(): ?ContentContainerActiveRecord
    {
        if ($this->model instanceof ContentContainerActiveRecord) {
            return $this->model;
        }

        if ($this->model instanceof AccountSettings) {
            return $this->model->user;
        }

        return null;
    }

    /**
     * Search tags data from Space/User containers for JSON response
     *
     * @param ContentContainerActiveRecord $contentContainer
     * @param string|null $keyword
     * @return array
     * @since 1.18.3
     */
    public static function searchTagsByContainer(ContentContainerActiveRecord $contentContainer, ?string $keyword): array
    {
        return static::searchTagsByContainerClass(
            get_class($contentContainer),
            $keyword,
            Topic::isAllowedToCreate($contentContainer),
        );
    }

    /**
     * Search tags data from Space/User containers for JSON response
     *
     * @param string $contentContainerClass
     * @param string $keyword
     * @return array
     */
    public static function searchTagsByContainerClass($contentContainerClass, $keyword, bool $allowAdd = true)
    {
        $keyword = trim($keyword);

        if ($keyword === '') {
            return [];
        }

        $containerTags = ContentContainerTag::find()
            ->select(['name AS id', 'name AS text'])
            ->where(['LIKE', 'name', $keyword])
            ->andWhere(['contentcontainer_class' => $contentContainerClass])
            ->asArray()
            ->all();

        if ($allowAdd) {
            $containerTags = array_merge([['id' => $keyword, 'text' => $keyword]], $containerTags);
        }

        return $containerTags;
    }
}
