<?php

namespace tests\codeception\unit\modules\web\security;

use humhub\modules\web\Module;
use humhub\modules\web\security\helpers\Security;
use humhub\modules\web\security\models\SecuritySettings;
use ReflectionClass;
use web\WebSecurityTest;
use Yii;

class ReportOnlyHeaderTest extends WebSecurityTest
{
    private const SHIPPED_POLICY = "script-src {{ nonce }} 'self'; object-src 'none'; base-uri 'self'; frame-ancestors 'self'; {{ reporting }}";

    public function _before()
    {
        parent::_before();

        /** @var Module $module */
        $module = Yii::$app->getModule('web');
        $module->enableCspReportOnly = false;
        $module->cspReportOnly = self::SHIPPED_POLICY;

        Yii::$app->response->headers->removeAll();
    }

    public function testShippedDefaults()
    {
        $defaults = (new ReflectionClass(Module::class))->getDefaultProperties();

        $this->assertFalse($defaults['enableCspReportOnly']);
        $this->assertSame(self::SHIPPED_POLICY, $defaults['cspReportOnly']);
    }

    public function testDisabledSendsNoReportOnlyHeader()
    {
        $this->setConfigFile('security.strict.json');
        Security::applyHeader(true);

        $headers = Yii::$app->response->headers;
        $this->assertNull($headers->get(SecuritySettings::HEADER_CONTENT_SECRUITY_POLICY_REPORT_ONLY));
        $this->assertNull($headers->get(SecuritySettings::HEADER_REPORTING_ENDPOINTS));
        $this->assertFalse(SecuritySettings::isReportingEnabled());
    }

    public function testEnabledSendsExpandedReportOnlyHeader()
    {
        $this->setConfigFile('security.strict.json');
        $this->enableReportOnly();
        Security::applyHeader(true);

        $headers = Yii::$app->response->headers;
        $nonce = Security::getNonce();
        $this->assertNotEmpty($nonce);
        $this->assertSame(
            "script-src 'nonce-" . $nonce . "' 'self'; object-src 'none'; base-uri 'self'; frame-ancestors 'self'; "
            . $this->expectedReportOnlyReporting(),
            $headers->get(SecuritySettings::HEADER_CONTENT_SECRUITY_POLICY_REPORT_ONLY),
        );
        $this->assertStringContainsString($nonce, $headers->get(SecuritySettings::HEADER_CONTENT_SECRUITY_POLICY));
        $this->assertSame(
            'csp-report-only-endpoint="' . SecuritySettings::getReportUrl(true) . '"',
            $headers->get(SecuritySettings::HEADER_REPORTING_ENDPOINTS),
        );
        $this->assertTrue(SecuritySettings::isReportingEnabled());
    }

    public function testNonceIsEmptyWithoutNonceSupport()
    {
        // security.default.json sets csp.nonce = false
        $this->enableReportOnly();
        Security::applyHeader(true);

        $this->assertStringStartsWith(
            "script-src  'self';",
            Yii::$app->response->headers->get(SecuritySettings::HEADER_CONTENT_SECRUITY_POLICY_REPORT_ONLY),
        );
    }

    public function testEnabledReplacesLegacyReportOnlySection()
    {
        $this->setConfigFile('security.reportonly2.json');
        $this->enableReportOnly();
        Security::applyHeader(true);

        $values = Yii::$app->response->headers->get(SecuritySettings::HEADER_CONTENT_SECRUITY_POLICY_REPORT_ONLY, null, false);
        $this->assertCount(1, $values);
        $this->assertStringContainsString('report-to csp-report-only-endpoint', $values[0]);
    }

    public function testLegacyReportOnlySectionUnchangedWhenDisabled()
    {
        $this->setConfigFile('security.reportonly2.json');
        Security::applyHeader(true);

        $values = Yii::$app->response->headers->get(SecuritySettings::HEADER_CONTENT_SECRUITY_POLICY_REPORT_ONLY, null, false);
        $this->assertCount(1, $values);
        $this->assertStringContainsString('report-uri ' . SecuritySettings::getReportUrl(true), $values[0]);
        $this->assertStringNotContainsString('report-to', $values[0]);
        $this->assertNull(Yii::$app->response->headers->get(SecuritySettings::HEADER_REPORTING_ENDPOINTS));
    }

    public function testReportingPlaceholderInEnforcedHeader()
    {
        $this->setConfigFile('security.headers-reporting.json');
        Security::applyHeader(true);

        $headers = Yii::$app->response->headers;
        $this->assertSame(
            "default-src *; script-src 'nonce-" . Security::getNonce() . "' 'self'; " . $this->expectedReporting(),
            $headers->get(SecuritySettings::HEADER_CONTENT_SECRUITY_POLICY),
        );
        $this->assertSame(
            'csp-endpoint="' . SecuritySettings::getReportUrl() . '"',
            $headers->get(SecuritySettings::HEADER_REPORTING_ENDPOINTS),
        );
        $this->assertNull($headers->get(SecuritySettings::HEADER_CONTENT_SECRUITY_POLICY_REPORT_ONLY));
        $this->assertTrue(SecuritySettings::isReportingEnabled());
    }

    public function testBothPoliciesListBothEndpoints()
    {
        $this->setConfigFile('security.headers-reporting.json');
        $this->enableReportOnly();
        Security::applyHeader(true);

        $headers = Yii::$app->response->headers;
        $this->assertSame(
            'csp-endpoint="' . SecuritySettings::getReportUrl() . '", csp-report-only-endpoint="' . SecuritySettings::getReportUrl(true) . '"',
            $headers->get(SecuritySettings::HEADER_REPORTING_ENDPOINTS),
        );
        $this->assertStringContainsString('report-to csp-endpoint', $headers->get(SecuritySettings::HEADER_CONTENT_SECRUITY_POLICY));
        $this->assertStringContainsString('report-to csp-report-only-endpoint', $headers->get(SecuritySettings::HEADER_CONTENT_SECRUITY_POLICY_REPORT_ONLY));
    }

    public function testReportOnlyUrlCarriesDisposition()
    {
        $this->assertStringContainsString('disposition=report', SecuritySettings::getReportUrl(true));
        $this->assertStringNotContainsString('disposition=report', SecuritySettings::getReportUrl());
    }

    public function testCustomPolicyWithoutPlaceholderSendsNoReportingEndpoints()
    {
        $this->setConfigFile('security.strict.json');
        $this->enableReportOnly();
        Yii::$app->getModule('web')->cspReportOnly = "script-src 'self'";
        Security::applyHeader(true);

        $headers = Yii::$app->response->headers;
        $this->assertSame("script-src 'self'", $headers->get(SecuritySettings::HEADER_CONTENT_SECRUITY_POLICY_REPORT_ONLY));
        $this->assertNull($headers->get(SecuritySettings::HEADER_REPORTING_ENDPOINTS));
        $this->assertTrue(SecuritySettings::isReportingEnabled());
    }

    public function testReportUrlIsAbsolute()
    {
        $this->assertMatchesRegularExpression('#^https?://.+(/web/security-report|r=web%2Fsecurity-report)$#', SecuritySettings::getReportUrl());
    }

    private function enableReportOnly(): void
    {
        Yii::$app->getModule('web')->enableCspReportOnly = true;
    }

    private function expectedReporting(): string
    {
        return 'report-uri ' . SecuritySettings::getReportUrl() . '; report-to csp-endpoint';
    }

    private function expectedReportOnlyReporting(): string
    {
        return 'report-uri ' . SecuritySettings::getReportUrl(true) . '; report-to csp-report-only-endpoint';
    }
}
