<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

namespace humhub\modules\content\widgets;


use humhub\modules\content\components\ContentContainerActiveRecord;
use humhub\modules\content\models\ContentType;
use humhub\widgets\MultiSelectField;

class ContentTypePicker extends MultiSelectField
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
            $itemkey = $this->getItemKey($item);

            if($type->typeClass === $itemkey) {
                $icon = $type->getIcon();
                return empty($icon) ? $this->defaultIcon : $icon;
            }
        }
    }


}