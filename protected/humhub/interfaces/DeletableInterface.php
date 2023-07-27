<?php

/*
 * @link      https://www.humhub.org/
 * @copyright Copyright (c) 2023 HumHub GmbH & Co. KG
 * @license   https://www.humhub.com/licences
 */

namespace humhub\interfaces;

interface DeletableInterface
{
    /**
     * Checks if given item can be deleted.
     */
    public function canDelete($userId = null);

}
