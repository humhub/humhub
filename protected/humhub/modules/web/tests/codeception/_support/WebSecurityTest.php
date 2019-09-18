<?php


namespace web;


use humhub\modules\web\Module;
use humhub\modules\web\security\helpers\Security;
use tests\codeception\_support\HumHubDbTestCase;
use Yii;
use yii\helpers\Json;

class WebSecurityTest extends HumHubDbTestCase
{
    /**
     * @return Module
     */
    public function _before()
    {
        parent::_before();
        Security::setNonce(null);
        $this->setConfigFile('security.default.json');
    }

    protected function setConfigFile($configFile)
    {
        /** @var $module Module */
        $module = Yii::$app->getModule('web');
        $configFile = realpath(__DIR__.'/../data/security/'.$configFile);
        $module->security = Json::decode(file_get_contents($configFile));
    }
}
