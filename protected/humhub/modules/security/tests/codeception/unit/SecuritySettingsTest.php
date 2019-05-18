<?php

namespace tests\codeception\unit\modules\security\components;

use Yii;
use humhub\libs\Html;
use humhub\modules\security\helpers\Security;
use humhub\modules\security\Module;
use humhub\modules\security\models\SecuritySettings;
use tests\codeception\_support\HumHubDbTestCase;

class SecuritySettingsTest extends HumHubDbTestCase
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
    }

    private function setConfigFile($file)
    {
        /** @var $module Module */
        $module = Yii::$app->getModule('security');
        $module->customConfigFile = $file;
    }

    public function testDefaultConfig()
    {

        $this->setConfigFile('doesnotexist.json');
        $settings = new SecuritySettings();
        $this->assertEquals('max-age=31536000', $settings->getHeader('Strict-Transport-Security'));
        $this->assertEquals('1', $settings->getHeader('X-XSS-Protection'));
        $this->assertEquals('nosniff', $settings->getHeader('X-Content-Type-Options'));
        $this->assertNull($settings->getHeader('X-Frame-Options'));
        $this->assertFalse($settings->isNonceSupportActive());
    }

    public function testStrictConfig()
    {
        $settings = new SecuritySettings();
        $this->assertEquals('max-age=31536000', $settings->getHeader('Strict-Transport-Security'));
        $this->assertEquals('1', $settings->getHeader('X-XSS-Protection'));
        $this->assertEquals('nosniff', $settings->getHeader('X-Content-Type-Options'));
        $this->assertEquals('deny', $settings->getHeader('X-Frame-Options'));
    }

    public function testNonceConfig()
    {
        $settings = new SecuritySettings();
        $this->assertTrue($settings->isNonceSupportActive());

        // Create a new csp with nonce support
        $csp = $settings->getCSPHeader();
        $this->assertNotNull(Security::getNonce());

        $this->assertEquals($csp, $settings->getCSPHeader(false));

        $nonce = Security::getNonce();

        $settings = new SecuritySettings();

        // Make sure sure the nonce is only updated once a new csp was generated
        $this->assertEquals($nonce, Security::getNonce());

        $settings->getCSPHeader(true);
        $this->assertNotEquals($nonce, Security::getNonce());
    }

    public function testNonceHtmlOutput()
    {
        $this->assertEmpty(Html::nonce());

        $settings = new SecuritySettings();
        $settings->getCSPHeader();

        $nonce = Html::nonce();
        $this->assertNotEmpty($nonce);

        $csp = $settings->getCSPHeader(true);
        $newNonce =  Html::nonce();
        $this->assertNotEquals($nonce, $newNonce);

        $this->assertContains(Security::getNonce(), $csp);
        $this->assertContains($newNonce, Html::beginTag('script'));
        $this->assertContains($newNonce, Html::script('var a = test;'));
    }

    public function testCustomCSPHeader()
    {
        $this->setConfigFile('security.customcsp.json');
        $settings = new SecuritySettings();
        $this->assertEquals("default-src 'self'", $settings->getHeader('Content-Security-Policy'));
        $this->assertFalse($settings->isNonceSupportActive());

        $this->assertNotNull($settings->getCSPHeader(true));

        $this->assertEmpty(Html::nonce());
        $this->assertEmpty(Security::getNonce());
    }

    public function testNoCSPHeader()
    {
        $this->setConfigFile('security.empty.json');
        $settings = new SecuritySettings();
        $this->assertNull($settings->getHeader('Content-Security-Policy'));
        $this->assertFalse($settings->isNonceSupportActive());

        $this->assertNull($settings->getCSPHeader(true));

        $this->assertEmpty(Html::nonce());
        $this->assertEmpty(Security::getNonce());
    }
}
