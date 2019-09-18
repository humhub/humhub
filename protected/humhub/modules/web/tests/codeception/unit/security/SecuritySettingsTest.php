<?php

namespace tests\codeception\unit\modules\web\security;

use web\WebSecurityTest;
use Yii;
use humhub\libs\Html;
use humhub\modules\web\Module;
use humhub\modules\web\security\helpers\Security;
use humhub\modules\web\security\models\SecuritySettings;
use yii\helpers\Json;

class SecuritySettingsTest extends WebSecurityTest
{
    public function testDefaultConfig()
    {
        $this->setConfigFile('security.default.json');
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
        $this->setConfigFile('security.strict.json');
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

    public function testReportActive()
    {
        $this->setConfigFile('security.report.json');
        $settings = new SecuritySettings();
        $this->assertTrue($settings->isCspReportEnabled());
        $this->assertFalse($settings->isReportOnlyCSP());
        $this->assertTrue(SecuritySettings::isReportingEnabled());
    }

    public function testReportOnlyActive()
    {
        $this->setConfigFile('security.reportonly1.json');
        $settings = new SecuritySettings();
        $this->assertTrue($settings->isCspReportEnabled());
        $this->assertTrue($settings->isReportOnlyCSP());
        $this->assertTrue(SecuritySettings::isReportingEnabled());
    }

    public function testReportOnlyCSPSection()
    {
        $this->setConfigFile('security.reportonly2.json');
        $settings = new SecuritySettings(['cspSection' => SecuritySettings::CSP_SECTION_REPORT_ONLY]);
        $this->assertTrue($settings->isCspReportEnabled());
        $this->assertTrue($settings->isReportOnlyCSP());
        $this->assertTrue(SecuritySettings::isReportingEnabled());
    }
}
