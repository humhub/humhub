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
 * Date: 28.07.2017
 * Time: 17:47
 */

namespace humhub\tests\codeception\unit\components\access;


use humhub\components\access\ActionAccessValidator;

class TestActionValidator extends ActionAccessValidator
{
    protected function validate($rule)
    {
        if(!$rule['return']) {
            $this->access->code = 404;
            $this->access->reason = 'Not you again!';
            return false;
        }

        return true;
    }
}