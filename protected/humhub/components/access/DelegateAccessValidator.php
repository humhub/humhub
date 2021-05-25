<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */


namespace humhub\components\access;

class DelegateAccessValidator extends ActionAccessValidator
{
    public $owner;

    public $handler;

    /**
     * @var string Name of callback method to run after failed validation
     * @since 1.8
     */
    public $codeCallback;
    
    /**
     * @inheritDoc
     */
    protected function validate($rule)
    {
        $handler = $this->handler;
        return $this->owner->$handler($rule, $this);
    }
}
