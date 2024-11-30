<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\tests\codeception\unit\components\access;

use humhub\components\access\ActionAccessValidator;

class TestActionValidator extends ActionAccessValidator
{
    protected function validate($rule)
    {
        if (!$rule['return']) {
            $this->access->code = 404;
            $this->access->reason = 'Not you again!';
            return false;
        }
        return true;
    }
}
