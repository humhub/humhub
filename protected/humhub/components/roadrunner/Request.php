<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2021 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\components\roadrunner;

/**
 * @inheritDoc
 */
class Request extends \humhub\components\Request
{

    /**
     * @return false
     */
    public function getIsConsoleRequest()
    {
        return false;
    }

}
