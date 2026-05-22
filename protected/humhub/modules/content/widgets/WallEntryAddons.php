<?php

namespace humhub\modules\content\widgets;

use humhub\modules\content\components\ContentActiveRecord;
use humhub\modules\content\widgets\stream\WallStreamEntryOptions;
use humhub\widgets\BaseStack;
use yii\helpers\ArrayHelper;

/**
 * WallEntryAddonWidget is an instance of StackWidget for wall entries.
 *
 * This widget is used to add some widgets to a wall entry.
 * e.g. Likes or Comments.
 *
 * @package humhub.modules_core.wall.widgets
 */
class WallEntryAddons extends BaseStack
{
    /**
     * Object derived from ContentActiveRecord
     *
     * @var ContentActiveRecord
     */
    public $object = null;

    /**
     * @var WallStreamEntryOptions
     */
    public $renderOptions;

    /**
     * @inheritdoc
     */
    public function addWidget($className, $params = [], $options = [])
    {
        if ($this->renderOptions) {
            if ($this->renderOptions->isAddonDisabled($className)) {
                return;
            }

            if (is_array($this->renderOptions->getAddonWidgetOptions($className))) {
                $params = ArrayHelper::merge($params, $this->renderOptions->getAddonWidgetOptions($className));
            }
        }

        parent::addWidget($className, $params, $options);
    }

}
