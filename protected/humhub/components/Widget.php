<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\components;

use Yii;
use humhub\libs\WidgetCreateEvent;

/**
 * @inheritdoc
 * @property-read \humhub\modules\ui\view\components\View $view
 */
class Widget extends \yii\base\Widget
{

    /**
     * @event WidgetCreateEvent an event raised before creating a widget.
     */
    const EVENT_CREATE = 'create';

    /**
     * @var bool if set to false this widget won't be rendered
     */
    public $render = true;

    /**
     * @var string defines an optional layout
     */
    public $widgetLayout;

    /**
     * Creates a widget instance and runs it.
     *
     * The widget rendering result is returned by this method.
     * @param array $config name-value pairs that will be used to initialize the object properties
     * @return string the rendering result of the widget.
     * @throws \Exception
     */
    public static function widget($config = [])
    {
        if (!isset($config['class'])) {
            $config['class'] = get_called_class();
        }

        if (isset($config['render']) && $config['render'] === false) {
            return '';
        }

        \yii\base\Event::trigger(static::class, self::EVENT_CREATE, new WidgetCreateEvent($config));

        ob_start();
        ob_implicit_flush(false);
        try {
            /* @var $widget Widget */
            $widget = Yii::createObject($config);
            $out = '';
            if ($widget->beforeRun()) {
                $result = (empty($widget->widgetLayout)) ?  $widget->run() : $widget->render($widget->widgetLayout, $widget->getLayoutViewParams());
                $out = $widget->afterRun($result);
            }
        } catch (\Throwable $e) {
            // close the output buffer opened above if it has not been closed already
            if (ob_get_level() > 0) {
                ob_end_clean();
            }
            throw $e;
        }

        return ob_get_clean() . $out;
    }

    /**
     * Returns an array of view parameter used if [[layout]] is set.
     *
     * By default the actual widget output created by [[run()]] is set as `content` param.
     *
     * @return array
     */
    public function getLayoutViewParams()
    {
        return [
            'content' => $this->run()
        ];
    }

    /**
     * Process is a wrapper for the run method
     */
    public function process()
    {
        return $this->run();
    }
}
