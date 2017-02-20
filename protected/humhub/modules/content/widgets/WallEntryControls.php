<?php

namespace humhub\modules\content\widgets;

/**
 * This widget is responsible for rendering the context menu for wallentries.
 * 
 * The default context menu can be extended by overwriting the getContextMenu function of
 * the WallEntryWidget.
 */
class WallEntryControls extends \humhub\widgets\BaseStack
{

    /**
     * @var \humhub\modules\content\components\ContentActiveRecord
     */
    public $object;

    /**
     * @var \humhub\modules\content\models\WallEntry
     */
    public $wallEntryWidget;

    /**
     * @inheritdoc
     */
    public function run()
    {
        foreach ($this->wallEntryWidget->getContextMenu() as $menuItem) {
            if (!is_array($menuItem) || empty($menuItem)) {
                continue;
            }

            $linkDefinition = $this->getWallEntryLinkDefinition($menuItem);
            $this->addWidget($linkDefinition[0], $linkDefinition[1], $linkDefinition[2]);
        }

        return parent::run();
    }

    /**
     * Returns the widget definition for the given $menuItem.
     * The $menuItem can either be given as single array:
     * 
     * ['label' => 'mylabel', icon => 'fa-myicon', 'data-action-click' => 'myaction', ...]
     * 
     *  or as widget type definition:
     * 
     * [MyWidget::class, [...], [...]]
     * 
     * @param type $menuItem
     * @return type
     */
    protected function getWallEntryLinkDefinition($menuItem)
    {
        $result = [];
        if (\yii\helpers\ArrayHelper::isAssociative($menuItem)) { // ['label' => 'xy', 'icon' => ...] -> WallEntryControlLink
            $result[0] = WallEntryControlLink::class;
            $result[1] = ['options' => $menuItem];
            $result[2] = [];
            $result[2]['sortOrder'] = isset($menuItem['sortOrder']) ? $menuItem['sortOrder'] : null;
        } else { // [MyWidget::class, [..WidgetOptions..], [sortOrder..]] -> Widget type definition
            $result[0] = $menuItem[0];
            $result[1] = isset($menuItem[1]) ? $menuItem[1] : null;
            $result[2] = isset($menuItem[2]) ? $menuItem[2] : null;
        }
        return $result;
    }

    /**
     * Checks if the given $array is an associative array or not.
     * 
     * @param array $arr
     * @return boolean
     */
    function isAssoc($arr)
    {
        if (array() === $arr) {
            return false;
        }
        return array_keys($arr) !== range(0, count($arr) - 1);
    }

}
