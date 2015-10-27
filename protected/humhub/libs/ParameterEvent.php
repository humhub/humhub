<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\libs;

/**
 * This event holds references to parameters which can be modified.
 *
 * @author luke
 * @since 0.21
 */
class ParameterEvent extends \yii\base\Event
{

    /**
     * @var array the parameter references
     */
    public $parameters;

    /**
     * @inheritdoc
     */
    public function __construct($parameters)
    {
        $this->parameters = $parameters;
        $this->init();
    }

}
