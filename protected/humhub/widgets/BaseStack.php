<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\widgets;

use humhub\components\Widget;
use Yii;

/**
 * StackWidget is a widget which can hold a set of subwidgets.
 *
 * This is mainly used to build e.g. sidebars.
 * It allows adding or removing other widgets by events.
 *
 * @package humhub.widgets
 * @since 0.5
 * @author Luke
 */
class BaseStack extends Widget
{

    const EVENT_INIT = 'init';
    const EVENT_RUN = 'run';

    /**
     * Default value for the sort order if no sort order value is specified in the widget.
     */
    const DEFAULT_SORT_ORDER = 9000;

    /**
     * Holds all added widgets
     *
     * Array
     *  [0] Classname
     *  [1] Params Arrays
     *  [2] Additional Options e.g. sortOrder (0-10000)
     *
     * @var array
     */
    public $widgets = [];

    /**
     * Seperator HTML Code (glue)
     *
     * @var string
     */
    public $seperator = '';

    /**
     * Template for output
     * The placeholder {content} will used to add content.
     *
     * @var string
     */
    public $template = '{content}';

    /**
     * Initializes the sidebar widget.
     */
    public function init()
    {
        // Yii 2.0.11 introduced own init event
        if (version_compare(Yii::getVersion(), '2.0.11', '<')) {
            $this->trigger(self::EVENT_INIT);
        }

        parent::init();
    }

    /**
     * Runs the Navigation
     */
    public function run()
    {
        $this->trigger(self::EVENT_RUN);

        $content = '';

        $hasContent = false;
        foreach ($this->getWidgets() as $widget) {
            $widgetClass = $widget[0];

            $out = $widgetClass::widget($widget[1]);

            if (!empty($out)) {
                if ($hasContent) {
                    $content .= $this->seperator;
                }
                $hasContent = true;
                $content .= $out;
            }
        }

        return str_replace('{content}', $content, $this->template);
    }

    /**
     * Removes a widget from the stack
     *
     * @param string $className
     */
    public function removeWidget($className)
    {
        foreach ($this->widgets as $k => $widget) {
            if ($widget[0] === $className) {
                unset($this->widgets[$k]);
            }
        }
    }

    /**
     * Returns all widgets by sortorder
     *
     * @return array
     */
    protected function getWidgets()
    {

        usort($this->widgets, function ($a, $b) {
            $sortA = (isset($a[2]['sortOrder'])) ? $a[2]['sortOrder'] : static::DEFAULT_SORT_ORDER;
            $sortB = (isset($b[2]['sortOrder'])) ? $b[2]['sortOrder'] : static::DEFAULT_SORT_ORDER;

            if ($sortA == $sortB) {
                return 0;
            } elseif ($sortA < $sortB) {
                return -1;
            } else {
                return 1;
            }
        });

        return $this->widgets;
    }

    /**
     * Adds a new widget
     *
     * @param string $className
     * @param array $params widget definition
     * @param array $options extra option array with e.g. "sortOrder"
     */
    public function addWidget($className, $params = [], $options = [])
    {
        if (!isset($options['sortOrder'])) {
            $options['sortOrder'] = static::DEFAULT_SORT_ORDER;
        }

        $this->widgets[] = [
            $className,
            $params,
            $options
        ];
    }

}
