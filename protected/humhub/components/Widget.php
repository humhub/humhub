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

        if(isset($config['render']) && $config['render'] === false) {
           return;
        }

        \yii\base\Event::trigger(self::className(), self::EVENT_CREATE, new WidgetCreateEvent($config));

        ob_start();
        ob_implicit_flush(false);
        try {
            /* @var $widget Widget */
            $widget = Yii::createObject($config);
            $out = '';
            if ($widget->beforeRun()) {
                $result = $widget->run();
                $out = $widget->afterRun($result);
            }
        } catch (\Exception $e) {
            // close the output buffer opened above if it has not been closed already
            if (ob_get_level() > 0) {
                ob_end_clean();
            }
            throw $e;
        }

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
