<?php

namespace tests\codeception\unit\modules\web\security;

use web\WebSecurityTest;
use Yii;
use humhub\libs\Html;
use humhub\modules\web\security\helpers\Security;
use humhub\modules\web\security\models\SecuritySettings;

class SecurityTest extends WebSecurityTest
{
    public function testNonceHtmlOutput()
    {
        $this->assertEmpty(Html::nonce());

        $this->setConfigFile('security.strict.json');
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
}
