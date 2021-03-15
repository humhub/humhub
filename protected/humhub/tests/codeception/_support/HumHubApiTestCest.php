<?php

namespace tests\codeception\_support;

use humhub\modules\rest\Module;
use Yii;

class HumHubApiTestCest
{
    public function _before()
    {
        $this->enableRestModule();
    }

    protected function enableRestModule()
    {
        /* @var Module $module */
        $module = Yii::$app->getModule('rest');
        if (!$module) {
            return false;
        }

        Yii::$app->moduleManager->enableModules(['rest']);

        $module->settings->set('enabledForAllUsers', true);
        $module->settings->set('enableBasicAuth', true);
    }

    protected function isRestModuleEnabled(): bool
    {
        $enabledModules = Yii::$app->moduleManager->getEnabledModules();
        return isset($enabledModules['rest']);
    }
}
