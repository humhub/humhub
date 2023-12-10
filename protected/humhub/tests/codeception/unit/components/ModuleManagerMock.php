<?php

/*
 * @link      https://www.humhub.org/
 * @copyright Copyright (c) 2023 HumHub GmbH & Co. KG
 * @license   https://www.humhub.com/licences
 */

namespace humhub\tests\codeception\unit\components;

use humhub\components\ModuleManager;
use humhub\libs\ModuleInfo;

class ModuleManagerMock extends ModuleManager
{
    public function &myModules(): array
    {
        return $this->modules;
    }

    public function myEnabledModules(): array
    {
        return array_filter($this->modules, static fn(ModuleInfo $moduleInfo) => $moduleInfo->isRegistered);
    }

    public function myCoreModules(): array
    {
        return array_filter($this->modules, static fn(ModuleInfo $moduleInfo) => $moduleInfo->isCoreModule);
    }
}
