<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

/**
 * Created by PhpStorm.
 * User: buddha
 * Date: 30.07.2017
 * Time: 02:21
 */

namespace humhub\components\access;


class DelegateAccessValidator extends ActionAccessValidator
{
    public $owner;

    public $handler;

    protected function validate($rule)
    {
        $handler = $this->handler;
        return $this->owner->$handler($rule, $this);
    }
}