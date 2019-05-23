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
        Security::setNonce(null);
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
        $this->setConfigFile('security.strict.json');
        $settings = new SecuritySettings();
        $this->assertEquals('max-age=31536000', $settings->getHeader('Strict-Transport-Security'));
        $this->assertEquals('1; mode=block', $settings->getHeader('X-XSS-Protection'));
        $this->assertEquals('nosniff', $settings->getHeader('X-Content-Type-Options'));
        $this->assertEquals('deny', $settings->getHeader('X-Frame-Options'));
    }

    public function testNonceConfig()
    {
        $settings = new SecuritySettings();
        $this->assertTrue($settings->isNonceSupportActive());

        $this->assertNull(Security::getNonce());

        // Create a new csp with nonce support
        $csp = $settings->getCSPHeader();

        $this->assertNotNull(Security::getNonce());

        $this->assertContains(Security::getNonce(), $csp);

        // Make sure the csp/nonce does not change
        $this->assertEquals($csp, $settings->getCSPHeader());
    }

    public function testCustomCSPHeader()
    {
        $this->setConfigFile('security.customcsp.json');
        $settings = new SecuritySettings();
        $this->assertEquals("default-src 'self'", $settings->getHeader('Content-Security-Policy'));
        $this->assertFalse($settings->isNonceSupportActive());

        $this->assertEquals("default-src 'self'", $settings->getCSPHeader());
        $this->assertEquals("testValue", $settings->getHeader("My-Test-Header"));

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

    public function testSingleReportOnly()
    {
        $this->setConfigFile('security.empty.json');
        $settings = new SecuritySettings();
        $this->assertNull($settings->getHeader('Content-Security-Policy'));
        $this->assertFalse($settings->isNonceSupportActive());

        $this->assertNull($settings->getCSPHeader());

        $this->assertEmpty(Html::nonce());
        $this->assertEmpty(Security::getNonce());
    }
}
