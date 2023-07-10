<?php

/*
 * @link      https://www.humhub.org/
 * @copyright Copyright (c) 2023 HumHub GmbH & Co. KG
 * @license   https://www.humhub.com/licences
 */

namespace humhub\interfaces;

use humhub\modules\user\models\User;

interface ReadableInterface
{
    /**
     * Checks if given element can be read.
     *
     * @param string|User $userId
     *
     * @return bool
     */
    public function canRead($userId = ""): bool;
}
