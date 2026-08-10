<?php

/*
 * @link      https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license   https://www.humhub.com/licences
 */

namespace humhub\tests\codeception\unit\components;

use humhub\components\ThemeVariables;

class ThemeVariablesMock extends ThemeVariables
{
    public int $storeCount = 0;

    protected function storeVariables(): void
    {
        $this->storeCount++;

        parent::storeVariables();
    }
}
