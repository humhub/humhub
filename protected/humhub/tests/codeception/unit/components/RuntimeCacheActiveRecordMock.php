<?php

/*
 * @link      https://www.humhub.org/
 * @copyright Copyright (c) 2023 HumHub GmbH & Co. KG
 * @license   https://www.humhub.com/licences
 */

namespace humhub\tests\codeception\unit\components;

use humhub\components\ActiveRecord;

/**
 * @since 1.15
 *
 * @noinspection UndetectableTableInspection
 */
class RuntimeCacheActiveRecordMock extends ActiveRecord
{
    public static string $tableName;

    public static function tableName()
    {
        return static::$tableName;
    }
}
