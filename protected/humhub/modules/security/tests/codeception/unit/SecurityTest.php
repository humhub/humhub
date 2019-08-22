<?php

namespace tests\codeception\unit\modules\security\components;

use Yii;
use humhub\libs\Html;
use humhub\modules\security\helpers\Security;
use humhub\modules\security\Module;
use humhub\modules\security\models\SecuritySettings;
use tests\codeception\_support\HumHubDbTestCase;

class SecurityTest extends HumHubDbTestCase
{

    /**
     * @return Module
     */
    public function _before()
    {
        parent::_before();

        /** @var $module Module */
        $module = Yii::$app->getModule('security');
        $module->configPath = '@security/tests/codeception/data';
        SecuritySettings::flushCache();
        Security::setNonce(null);
    }

    public function testNonceHtmlOutput()
    {
        $this->assertEmpty(Html::nonce());

        $settings = new SecuritySettings();
        $csp = $settings->getCSPHeader();

        $nonce = Html::nonce();
        $this->assertNotEmpty($nonce);

        $this->assertContains(Security::getNonce(), $csp);
        $this->assertContains($nonce, Html::beginTag('script'));
        $this->assertContains($nonce, Html::script('var a = test;'));
    }

    public function testHttpHeader()
    {
        $this->setConfigFile('security.strict.json');
        Security::applyHeader(true);

        $this->assertContains(Security::getNonce(), Yii::$app->response->headers->get(SecuritySettings::HEADER_CONTENT_SECRUITY_POLICY));
        $this->assertEquals(Yii::$app->response->headers->get(SecuritySettings::HEADER_STRICT_TRANSPORT_SECURITY),  'max-age=31536000');
        $this->assertEquals(Yii::$app->response->headers->get(SecuritySettings::HEADER_X_XSS_PROTECTION), '1; mode=block');
        $this->assertEquals(Yii::$app->response->headers->get(SecuritySettings::HEADER_X_CONTENT_TYPE), 'nosniff');
        $this->assertEquals(Yii::$app->response->headers->get(SecuritySettings::HEADER_X_FRAME_OPTIONS), 'deny');
        $this->assertEquals(Yii::$app->response->headers->get(SecuritySettings::HEADER_REFERRER_POLICY), 'no-referrer-when-downgrade');
        $this->assertEquals(Yii::$app->response->headers->get(SecuritySettings::HEADER_X_PERMITTED_CROSS_DOMAIN_POLICIES), 'master-only');
        $this->assertEquals(Yii::$app->response->headers->get('My-Custom-Security-Header'), 'test');
    }

    private function setConfigFile($file)
    {
        /** @var $module Module */
        $module = Yii::$app->getModule('security');
        $module->customConfigFile = $file;
    }


}
