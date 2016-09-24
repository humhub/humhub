<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\components;

use Yii;
use yii\base\Event;
use humhub\libs\WidgetCreateEvent;

/**
 * @inheritdoc
 */
class Widget extends \yii\base\Widget
{

    /**
     * @event WidgetCreateEvent an event raised before creating a widget.
     */
    const EVENT_CREATE = 'create';

    /**
     * @event Event an event raised after run a widget.
     */
    const EVENT_AFTER_RUN = 'run';

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

        Event::trigger(self::className(), self::EVENT_CREATE, new WidgetCreateEvent($config));

        ob_start();
        ob_implicit_flush(false);
        try {
            /* @var $widget Widget */
            $widget = Yii::createObject($config);
            $out = $widget->process();
        } catch (\Exception $e) {
            // close the output buffer opened above if it has not been closed already
            if (ob_get_level() > 0) {
                ob_end_clean();
            }
            throw $e;
        }

        $widget->trigger(self::EVENT_AFTER_RUN);
        return ob_get_clean() . $out;
    }

    /**
     * Process is a wrapper for the run method
     */
    public function process()
    {
        return $this->run();
    }

}
