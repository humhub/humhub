<?php

/*
 * @link      https://www.humhub.org/
 * @copyright Copyright (c) 2023 HumHub GmbH & Co. KG
 * @license   https://www.humhub.com/licences
 */

namespace humhub\tests\codeception\unit\components;

use humhub\components\ModuleManager;

class ModuleManagerMock extends ModuleManager
{
    public function &myModules(): array
    {
        return $this->modules;
    }

    public function &myEnabledModules(): array
    {
        return $this->enabledModules;
    }

    public function &myCoreModules(): array
    {
        return $this->coreModules;
    }
}
