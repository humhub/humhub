<?php

/**
 * HumHub
 * Copyright © 2014 The HumHub Project
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 */

namespace humhub\widgets;

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
class BaseStack extends \yii\base\Widget
{

    const EVENT_INIT = 'init';
    const EVENT_RUN = 'run';

    /**
     * Holds all added widgets
     *
     * Array
     *  [0] Classname
     *  [1] Params Arrays
     *  [2] Additional Options
     *
     * @var array
     */
    public $widgets = array();

    /**
     * Seperator HTML Code (glue)
     *
     * @var string
     */
    public $seperator = "";

    /**
     * Template for output
     * The placeholder {content} will used to add content.
     *
     * @var string
     */
    public $template = "{content}";

    /**
     * Initializes the sidebar widget.
     */
    public function init()
    {
        $this->trigger(self::EVENT_INIT);
        parent::init();
    }

    /**
     * Runs the Navigation
     */
    public function run()
    {
        $this->trigger(self::EVENT_RUN);

        $content = "";

        $i = 0;
        foreach ($this->getWidgets() as $widget) {
            $i++;

            $widgetClass = $widget[0];

            $out = $widgetClass::widget($widget[1]);

            if ($out != "") {
                $content .= $out;
                if ($i != count($this->getWidgets()))
                    $content .= $this->seperator;
            }
        }

        print str_replace('{content}', $content, $this->template);
    }

    /**
     * Removes a widget from the stack
     *
     * @param string $className
     */
    public function removeWidget($className)
    {
        foreach($this->widgets as $k => $widget) {
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

        usort($this->widgets, function($a, $b) {
            $sortA = (isset($a[2]['sortOrder'])) ? $a[2]['sortOrder'] : 100;
            $sortB = (isset($b[2]['sortOrder'])) ? $b[2]['sortOrder'] : 100;

            if ($sortA == $sortB) {
                return 0;
            } else if ($sortA < $sortB) {
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
    public function addWidget($className, $params = array(), $options = array())
    {
        if (!isset($options['sortOrder']))
            $options['sortOrder'] = 100;

        $this->widgets[] = array(
            $className,
            $params,
            $options
        );
    }

}
