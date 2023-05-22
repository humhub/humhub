<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

namespace humhub\modules\content\widgets;

use humhub\modules\content\helpers\ContentContainerHelper;
use humhub\modules\content\components\ContentContainerActiveRecord;
use humhub\modules\content\models\ContentType;
use humhub\modules\ui\form\widgets\MultiSelect;
use Yii;

class ContentTypePicker extends MultiSelect
{
    /**
     * @var ContentContainerActiveRecord|null
     */
    public $contentContainer;

    /**
     * @var ContentType[] available types by contentContainer
     */
    public $types = [];

    /**
     * @var string icon used for content types without own icon definition
     */
    public $defaultIcon = 'fa-filter';

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->placeholderMore = Yii::t('ContentModule.base', 'Select type...');

        $this->contentContainer = $this->contentContainer ? $this->contentContainer : ContentContainerHelper::getCurrent();

        parent::init();
        $this->items = ContentType::getContentTypeSelection($this->contentContainer);
        $this->types = ContentType::getContentTypes($this->contentContainer);
    }

    /**
     * @inheritdoc
     */
    protected function getItemImage($item)
    {
        foreach ($this->types as $type) {
            $itemKey = $this->getItemKey($item);

            if ($type->typeClass === $itemKey) {
                $icon = $type->getIcon();
                return empty($icon) ? $this->defaultIcon : $icon;
            }
        }
    }

    /**
     * @inheritdoc
     */
    protected function getItemText($item)
    {
        return ucfirst(parent::getItemText($item));
    }

}
