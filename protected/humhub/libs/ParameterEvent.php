<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\libs;

use yii\base\Event;

/**
 * This event holds references to parameters which can be modified.
 *
 * @author luke
 * @since 0.21
 */
class ParameterEvent extends Event
{
    /**
     * @inheritdoc
     * @param mixed[] $parameters
     */
    public function __construct(/**
     * @var array the parameter references
     */
    public $parameters)
    {
        $this->init();
    }
}
