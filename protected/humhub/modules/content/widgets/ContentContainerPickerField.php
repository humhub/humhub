<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

namespace humhub\modules\content\widgets;

use humhub\modules\content\models\ContentContainer;
use humhub\modules\ui\form\widgets\BasePicker;

/**
 * Mutliselect input field for selecting Space guids or current user Profile
 *
 * This widget has no default route, so the `url` param is required.
 *
 * @since 1.17.2
 */
class ContentContainerPickerField extends BasePicker
{
    /**
     * @inheritdoc
     * The 'guid' value is default for UserPickerField
     */
    public $itemKey = 'guid';

    /**
     * @inheritdoc
     */
    public $itemClass = ContentContainer::class;

    /**
     * @inheritdoc
     */
    protected function getAttributes()
    {
        return array_merge(parent::getAttributes(), [
            'data-tags' => 'false',
        ]);
    }

    /**
     * @inheritdoc
     */
    protected function getItemText($item)
    {
        return $this->itemClass === ContentContainer::class
            ? $item->getPolymorphicRelation()->displayName
            : $item->displayName;
    }

    /**
     * @inheritdoc
     */
    protected function getItemImage($item)
    {
        return $this->itemClass === ContentContainer::class
            ? $item->getPolymorphicRelation()->getProfileImage()->getUrl()
            : $item->getProfileImage()->getUrl();
    }
}
