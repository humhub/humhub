<?php

/*
 * @link      https://www.humhub.org/
 * @copyright Copyright (c) 2023 HumHub GmbH & Co. KG
 * @license   https://www.humhub.com/licences
 */

namespace humhub\interfaces;

interface GlobalActiveRecordInterface extends ActiveRecordInterface
{
    /**
     * @return string Class name of the module the implementing class belongs to
     */
    public static function moduleId(): string;
}
