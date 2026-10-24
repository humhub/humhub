<?php

namespace humhub\modules\content\widgets;

use humhub\modules\content\components\ContentActiveRecord;
use humhub\modules\content\widgets\stream\StreamEntryOptions;
use humhub\modules\content\widgets\stream\WallStreamEntryOptions;
use humhub\modules\content\widgets\stream\WallStreamEntryWidget;
use humhub\widgets\menu\MenuEntry;
use humhub\widgets\menu\WidgetMenuEntry;
use humhub\widgets\menu\Menu;
use Yii;
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
        if (!$this->renderOptions && $this->wallEntryWidget instanceof WallStreamEntryWidget) {
            $this->renderOptions = $this->wallEntryWidget->renderOptions;
        } elseif (!$this->renderOptions) {
            $this->renderOptions = new WallStreamEntryOptions();
        }
    }

    /**
     * @return string the active view context of the stream entry
     */
    public function getViewContext()
    {
        if (!$this->renderOptions) {
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
        if ($this->renderOptions && $this->renderOptions->isContextMenuEntryDisabled($entry)) {
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
            'class' => 'nav nav-pills preferences',
        ];
    }

    /**
     * Adds an entry by widget class and parameters and entry options
     *
     * @param string $className widget class
     * @param array $params widget options
     * @param array $options entry options
     */
    public function addWidget($className, $params = [], $options = [])
    {
        $this->addEntry($this->createEntry($className, $params, $options));
    }

    /**
     * The menu entry for a `[class, params, options]` definition.
     *
     * A {@see MenuEntry} class is instantiated with the params and options as its own
     * configuration; that is what core's control links ({@see DeleteLink}, {@see EditLink}, …)
     * are since 1.20, and what keeps the definitions modules pass around
     * (`[EditLink::class, ['model' => …], ['sortOrder' => 100]]`) working. Any other class is
     * a widget and wrapped in a {@see WidgetMenuEntry}.
     *
     * @param string $className
     * @param array|null $params
     * @param array $options entry options such as `sortOrder` or `id`
     * @since 1.20
     */
    protected function createEntry($className, $params = [], $options = []): MenuEntry
    {
        $options['sortOrder'] ??= PHP_INT_MAX;

        if (is_string($className) && is_subclass_of($className, MenuEntry::class)) {
            return Yii::createObject(array_merge(['class' => $className], $params ?: [], $options));
        }

        return new WidgetMenuEntry(array_merge($options, ['widgetClass' => $className, 'widgetOptions' => $params]));
    }

    /**
     * Returns the widget definition for the given $menuItem.
     * The $menuItem can either be given as single array:
     *
     * ['label' => 'mylabel', icon => 'myicon', 'data-action-click' => 'myaction', ...]
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
        if ($menuItem instanceof MenuEntry) {
            return $menuItem;
        }

        if (ArrayHelper::isAssociative($menuItem)) { // ['label' => 'xy', 'icon' => ...] -> WallEntryControlLink
            $widgetClass = WallEntryControlLink::class;
            $widgetOptions = ['options' => $menuItem];
            $options = ['sortOrder' => $menuItem['sortOrder'] ?? PHP_INT_MAX];
        } else { // [MyWidget::class, [..WidgetOptions..], [sortOrder..]] -> Widget type definition
            $widgetClass = $menuItem[0];
            $widgetOptions = $menuItem[1] ?? null;
            $options = $menuItem[2] ?? [];
        }

        return $this->createEntry($widgetClass, $widgetOptions, $options);
    }

    /**
     * Checks if the given $array is an associative array or not.
     *
     * @param array $arr
     * @return bool
     */
    public function isAssoc($arr)
    {
        if ([] === $arr) {
            return false;
        }
        return array_keys($arr) !== range(0, count($arr) - 1);
    }

}
