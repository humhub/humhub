<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\libs;

use yii\base\Event;

/**
 * WidgetCreateEvent is raised before creating a widget
 *
 * @see \humhub\components\Widget
 * @author luke
 */
class WidgetCreateEvent extends Event
{

    /**
     * @var array Reference to the config of widget create
     */
    public $config;

    /**
     * @inheritdoc
     */
    public function __construct(&$attributes)
    {
        $this->config = &$attributes;
        $this->init();
    }

}
