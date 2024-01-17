<?php

/*
 * @link      https://www.humhub.org/
 * @copyright Copyright (c) 2018-2023 HumHub GmbH & Co. KG
 * @license   https://www.humhub.com/licences
 */

/**
 * @noinspection PhpIllegalPsrClassPathInspection
 */

namespace humhub\tests\codeception\unit\helpers;

use humhub\helpers\DataTypeHelper;

/**
 * Class DataTypeHelperTest
 */
class DataTypeHelperMock extends DataTypeHelper
{
    public static function checkTypeHelper(&$input, string $inputType, $typeToCheck): ?string
    {
        return parent::checkTypeHelper($input, $inputType, $typeToCheck);
    }

    public static function parseTypes($types): array
    {
        return parent::parseTypes($types);
    }
}
