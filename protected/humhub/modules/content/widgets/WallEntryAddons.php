<?php

namespace humhub\modules\content\widgets;

use humhub\modules\content\components\ContentActiveRecord;
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
     * Can be set to overwrite or extend the widget options of a given addon widget as:
     *
     * ```
     * $widgetOptions = [
     *      MyAddonWidget::class => [
     *          'someOption' => false
     *      ]
     * ]
     * ```
     * @var array
    */
    public $widgetOptions = [];

    /**
     * @inheritdoc
     */
    public function addWidget($className, $params = [], $options = [])
    {
        if(isset($this->widgetOptions[$className])) {
            $params = ArrayHelper::merge($params, $this->widgetOptions[$className]);
        }

        parent::addWidget($className, $params, $options);
    }

}

?>
