<?php

namespace humhub\modules\content\widgets;

use humhub\modules\content\components\ContentActiveRecord;
use humhub\modules\content\widgets\stream\StreamEntryOptions;
use humhub\modules\content\widgets\stream\WallStreamEntryOptions;
use humhub\modules\content\widgets\stream\WallStreamEntryWidget;
use humhub\modules\ui\menu\MenuEntry;
use humhub\modules\ui\menu\widgets\Menu;
use yii\helpers\ArrayHelper;

/**
 * This widget is responsible for rendering the context menu for wallentries.
 *
 * The default context menu can be extended by overwriting the getContextMenu function of
 * the WallEntryWidget.
 */
class WallEntryControls extends Menu
{

    /**
     * @var ContentActiveRecord
     */
    public $object;

    /**
     * @var WallEntry|WallStreamEntryWidget
     */
    public $wallEntryWidget;

    /**
     * @inheritdoc
     */
    public $template = '@content/widgets/views/wallEntryControls';

    /**
     * @var WallStreamEntryOptions
     */
    public $renderOptions;

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->initRenderOptions();
        parent::init();
    }

    public function initRenderOptions()
    {
        if(!$this->renderOptions && $this->wallEntryWidget instanceof WallStreamEntryWidget) {
            $this->renderOptions = $this->wallEntryWidget->renderOptions;
        } else if(!$this->renderOptions) {
            $this->renderOptions = new WallStreamEntryOptions();
        }
    }

    /**
     * @return string the active view context of the stream entry
     */
    public function getViewContext()
    {
        if(!$this->renderOptions) {
            return StreamEntryOptions::VIEW_CONTEXT_DEFAULT;
        }

        return $this->renderOptions->getViewContext();
    }

    public function initControls()
    {
        if ($this->renderOptions->isControlsMenuDisabled()) {
            return;
        }

        $entries = $this->wallEntryWidget instanceof WallEntry
            ? $this->wallEntryWidget->getContextMenu()
            : $this->wallEntryWidget->getControlsMenuEntries();

        foreach ($entries as $menuItem) {
            if (empty($menuItem)) {
                continue;
            }

            $this->addEntry($this->getWallEntryLink($menuItem));
        }
    }

    /**
     * @inheritdoc
     */
    public function run()
    {
        if ($this->renderOptions->isControlsMenuDisabled()) {
            return '';
        }

        $this->initControls();

        return parent::run();
    }

    /**
     * @inheritdoc
     */
    public function addEntry(MenuEntry $entry)
    {
        if($this->renderOptions && $this->renderOptions->isContextMenuEntryDisabled($entry)) {
            return;
        }

        parent::addEntry($entry);
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

    /**
     * Adds an entry by widget class and parameters and entry options
     *
     * @param string $className widget class
     * @param array $params widget options
     * @param array $options entry options
     */
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
