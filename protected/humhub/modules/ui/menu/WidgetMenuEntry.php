<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\ui\menu;

use Exception;
use humhub\modules\ui\menu\widgets\Menu;
use Throwable;
use Yii;
use yii\base\Widget;

/**
 * Class WidgetMenuEntry
 *
 * Widget based menu entry
 *
 * @since 1.4
 * @see Menu
 */
class WidgetMenuEntry extends MenuEntry
{
    public $widgetClass;

    public $widgetOptions;

    /**
     * Renders the link tag for this menu entry
     *
     * @param array $extraHtmlOptions
     * @return string the Html link
     */
    public function renderEntry($extraHtmlOptions = [])
    {
        try {
            return call_user_func($this->widgetClass . '::widget', $this->widgetOptions);
        } catch (Exception $e) {
            Yii::error($e);
        }
    }

    /**
     * @inheritDoc
     * @since 1.7
     */
    public function getEntryClass()
    {
        return $this->widgetClass;
    }

    /**
     * @inheritdoc
     *
     * A widget entry is a class plus a config, not data, so whether it can be described at
     * all is the widget's own call: it is described when the widget implements
     * {@see DescribableWidget}, and left to be rendered otherwise.
     *
     * Describing instantiates the widget (which runs its `init()`, where a control link
     * resolves its label, icon and action) but never runs it, so nothing is rendered and no
     * widget lifecycle event fires.
     *
     * @since 1.20
     */
    public function describe(): ?array
    {
        $widget = $this->createWidget();

        if (!$widget instanceof DescribableWidget) {
            return null;
        }

        try {
            $descriptor = $widget->describeMenuEntry();
        } catch (Throwable $e) {
            Yii::error($e);
            return null;
        }

        if ($descriptor === null) {
            return null;
        }

        $descriptor += ['id' => null, 'sortOrder' => $this->getSortOrder()];

        if ($descriptor['id'] === null) {
            $descriptor['id'] = $this->getId() ?: static::describeIdFor((string)$this->widgetClass);
        }

        return $descriptor;
    }

    /**
     * Instantiates the wrapped widget without running it.
     *
     * {@see Yii::createObject()} runs the widget's `init()` as part of construction, which is
     * exactly the part a describable widget needs — a control link normalizes its label, icon
     * and `data-action-*` options there.
     *
     * @since 1.20
     */
    protected function createWidget(): ?Widget
    {
        if (!is_string($this->widgetClass) || !class_exists($this->widgetClass)) {
            return null;
        }

        try {
            $config = is_array($this->widgetOptions) ? $this->widgetOptions : [];
            $config['class'] = $this->widgetClass;
            $widget = Yii::createObject($config);
        } catch (Throwable $e) {
            Yii::error($e);
            return null;
        }

        return $widget instanceof Widget ? $widget : null;
    }

    /**
     * The fallback entry id for a widget entry that carries none of its own: the widget's
     * short class name in kebab case (`ContentTopicButton` → `content-topic-button`).
     *
     * Stable across requests, which is what a client needs in order to override or remove the
     * entry by id — but NOT unique when the same widget class is contributed more than once
     * (`share-between-humhub` adds one `ShareLink` per configured site). Disambiguating those
     * is the resolving caller's job, since only it sees the whole menu.
     *
     * @since 1.20
     */
    public static function describeIdFor(string $widgetClass): string
    {
        $shortName = substr((string)strrchr('\\' . $widgetClass, '\\'), 1);

        return strtolower(preg_replace('/([a-z0-9])([A-Z])/', '$1-$2', $shortName));
    }
}
