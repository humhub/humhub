<?php

namespace humhub\modules\content\widgets;

use humhub\modules\ui\menu\MenuEntry;
use humhub\modules\ui\menu\widgets\Menu;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

/**
 * This widget is responsible for rendering the context menu for wallentries.
 *
 * The default context menu can be extended by overwriting the getContextMenu function of
 * the WallEntryWidget.
 */
class WallEntryControls extends Menu
{

    /**
     * @var \humhub\modules\content\components\ContentActiveRecord
     */
    public $object;

    /**
     * @var WallEntry
     */
    public $wallEntryWidget;

    /**
     * @inheritdoc
     */
    public $template = '@content/widgets/views/wallEntryControls';

    /**
     * @inheritdoc
     */
    public function run()
    {
        foreach ($this->wallEntryWidget->getContextMenu() as $menuItem) {
            if (empty($menuItem)) {
                continue;
            }

            $this->addEntry($this->getWallEntryLink($menuItem));
        }

        return parent::run();
    }

    /**
     * @inheritdoc
     */
    public function getAttributes()
    {
        return [
            'class' => 'nav nav-pills preferences'
        ];
    }

    public function addWidget($className, $params = [], $options = []) {
        $sortOrder = isset($options['sortOrder']) ? $options['sortOrder'] : PHP_INT_MAX;
        $cfg = array_merge($options, ['widgetClass' => $className, 'widgetOptions' => $params, 'sortOrder' => $sortOrder]);
        $this->addEntry(new LegacyWallEntryControlLink($cfg));
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
     * @param [] $menuItem
     * @return MenuEntry
     */
    protected function getWallEntryLink($menuItem)
    {
        if($menuItem instanceof MenuEntry) {
            return $menuItem;
        }

        $result = [];
        if (ArrayHelper::isAssociative($menuItem)) { // ['label' => 'xy', 'icon' => ...] -> WallEntryControlLink
            $widgetClass = WallEntryControlLink::class;
            $widgetOptions = ['options' => $menuItem];
            $options = ['sortOrder' => isset($menuItem['sortOrder']) ? $menuItem['sortOrder'] : PHP_INT_MAX];
        } else { // [MyWidget::class, [..WidgetOptions..], [sortOrder..]] -> Widget type definition
            $widgetClass = $menuItem[0];
            $widgetOptions = isset($menuItem[1]) ? $menuItem[1] : null;
            $options = isset($menuItem[2]) ? $menuItem[2] : [];
        }

        $sortOrder = isset($options['sortOrder']) ? $options['sortOrder'] : PHP_INT_MAX;
        $cfg = array_merge($options, ['widgetClass' => $widgetClass, 'widgetOptions' => $widgetOptions, 'sortOrder' => $sortOrder]);

        return new LegacyWallEntryControlLink($cfg);
    }

    /**
     * Checks if the given $array is an associative array or not.
     *
     * @param array $arr
     * @return boolean
     */
    function isAssoc($arr)
    {
        if ([] === $arr) {
            return false;
        }
        return array_keys($arr) !== range(0, count($arr) - 1);
    }

}
