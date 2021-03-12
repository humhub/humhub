<?php

use humhub\modules\rest\Module;

/**
 * Inherited Methods
 * @method void wantToTest($text)
 * @method void wantTo($text)
 * @method void execute($callable)
 * @method void expectTo($prediction)
 * @method void expect($prediction)
 * @method void amGoingTo($argumentation)
 * @method void am($role)
 * @method void lookForwardTo($achieveValue)
 * @method void comment($description)
 * @method \Codeception\Lib\Friend haveFriend($name, $actorClass = NULL)
 *
 * @SuppressWarnings(PHPMD)
 */
class ApiTester extends \Codeception\Actor
{
    use _generated\ApiTesterActions;

    public function isRestModuleEnabled(): bool
    {
        $restModuleId = 'rest';

        /* @var Module $module */
        $module = Yii::$app->getModule($restModuleId);
        if (!$module) {
            return false;
        }

        Yii::$app->moduleManager->enableModules([$restModuleId]);
        $enabledModules = Yii::$app->moduleManager->getEnabledModules();
        if (!isset($enabledModules[$restModuleId])) {
            return false;
        }

        $module->settings->set('enabledForAllUsers', true);
        $module->settings->set('enableBasicAuth', true);

        return true;
    }

    public function amAdmin()
    {
        $this->amUser('Admin', 'test');
    }

    public function amUser($user = null, $password = null)
    {
        $this->amHttpAuthenticated($user, $password);
    }
}
