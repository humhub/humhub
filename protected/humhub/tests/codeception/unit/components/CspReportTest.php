<?php

namespace humhub\tests\codeception\unit\components;

use humhub\components\CspReport;
use tests\codeception\_support\HumHubDbTestCase;
use yii\log\Logger;

class CspReportTest extends HumHubDbTestCase
{
    private const POLICY = "script-src 'nonce-SECRETNONCE123' 'self'";

    public function _before()
    {
        parent::_before();
        self::logInitialize();
        self::logFlush();
    }

    public function testLegacyReportOnly()
    {
        $reports = CspReport::parse($this->legacyBody());

        $this->assertCount(1, $reports);
        $this->assertTrue($reports[0]->isReportOnly());
        $this->assertSame(
            'CSP violation (report-only): script-src-elem https://cdn.example.org/x.js in https://example.com/dashboard',
            $reports[0]->getSummary(),
        );
    }

    public function testLegacyEnforced()
    {
        $reports = CspReport::parse($this->legacyBody(['disposition' => 'enforce']));

        $this->assertCount(1, $reports);
        $this->assertFalse($reports[0]->isReportOnly());
        $this->assertStringStartsWith('CSP violation (enforced): ', $reports[0]->getSummary());
    }

    public function testMissingDispositionCountsAsEnforced()
    {
        $reports = CspReport::parse($this->legacyBody(['disposition' => null]));

        $this->assertCount(1, $reports);
        $this->assertFalse($reports[0]->isReportOnly());
    }

    public function testReportOnlyUrlOverridesMissingDisposition()
    {
        $reports = CspReport::parse($this->legacyBody(['disposition' => null]), true);

        $this->assertCount(1, $reports);
        $this->assertTrue($reports[0]->isReportOnly());
        $this->assertStringStartsWith('CSP violation (report-only): ', $reports[0]->getSummary());
    }

    public function testReportOnlyUrlAppliesToReportingApiEntries()
    {
        $reports = CspReport::parse(json_encode([$this->reportingApiEntry(['disposition' => 'enforce'])]), true);

        $this->assertCount(1, $reports);
        $this->assertTrue($reports[0]->isReportOnly());
    }

    public function testReportingApiUsesOnlyCspViolations()
    {
        $reports = CspReport::parse(json_encode([
            $this->reportingApiEntry(),
            ['type' => 'deprecation', 'age' => 1, 'url' => 'https://example.com/', 'body' => ['id' => 'x']],
            $this->reportingApiEntry(['disposition' => 'enforce']),
        ]));

        $this->assertCount(2, $reports);
        $this->assertTrue($reports[0]->isReportOnly());
        $this->assertSame(
            'CSP violation (report-only): script-src-elem https://cdn.example.org/y.js in https://example.com/dashboard',
            $reports[0]->getSummary(),
        );
        $this->assertFalse($reports[1]->isReportOnly());
    }

    public function testSummaryFallsBackToViolatedDirective()
    {
        $reports = CspReport::parse($this->legacyBody([
            'effective-directive' => null,
            'blocked-uri' => null,
            'source-file' => null,
        ]));

        $this->assertSame('CSP violation (report-only): script-src-elem', $reports[0]->getSummary());
    }

    public function testSummaryStripsControlCharacters()
    {
        $reports = CspReport::parse($this->legacyBody(['blocked-uri' => "https://cdn.example.org/x.js\nFAKE LINE"]));

        $this->assertStringNotContainsString("\n", $reports[0]->getSummary());
    }

    /**
     * @dataProvider invalidBodies
     */
    public function testInvalidBodiesYieldNoReports(string $body)
    {
        $this->assertSame([], CspReport::parse($body));
    }

    public static function invalidBodies(): array
    {
        return [
            'empty' => [''],
            'no json' => ['not json'],
            'null' => ['null'],
            'string' => ['"report"'],
            'unknown object' => ['{"foo": 1}'],
            'empty list' => ['[]'],
            'violation without body' => ['[{"type": "csp-violation"}]'],
        ];
    }

    public function testOversizedBodyIsDiscarded()
    {
        $body = $this->legacyBody(['script-sample' => str_repeat('a', CspReport::MAX_BODY_SIZE)]);

        $this->assertGreaterThan(CspReport::MAX_BODY_SIZE, strlen($body));
        $this->assertSame([], CspReport::parse($body));
    }

    public function testLogLevels()
    {
        CspReport::parse($this->legacyBody())[0]->log();
        CspReport::parse($this->legacyBody(['disposition' => 'enforce']))[0]->log();

        $messages = self::logFilterMessages();
        $this->assertCount(2, $messages);
        $this->assertSame(Logger::LEVEL_WARNING, $messages[0][1]);
        $this->assertSame('web.security', $messages[0][2]);
        $this->assertSame(Logger::LEVEL_ERROR, $messages[1][1]);
        $this->assertSame('web.security', $messages[1][2]);
    }

    public function testLogMasksNonceInBothFormats()
    {
        CspReport::parse($this->legacyBody())[0]->log();
        CspReport::parse(json_encode([$this->reportingApiEntry()]))[0]->log();

        $messages = self::logFilterMessages();
        $this->assertCount(2, $messages);
        foreach ($messages as $message) {
            $this->assertStringStartsWith('CSP violation (report-only): ', $message[0]);
            $this->assertStringContainsString("'nonce-xxxxxxxxxxxxxxxxxxxxxxxx'", $message[0]);
            $this->assertStringNotContainsString('SECRETNONCE123', $message[0]);
        }
    }

    private function legacyBody(array $override = []): string
    {
        $report = array_merge([
            'document-uri' => 'https://example.com/dashboard',
            'effective-directive' => 'script-src-elem',
            'violated-directive' => 'script-src-elem',
            'blocked-uri' => 'https://cdn.example.org/x.js',
            'source-file' => 'https://example.com/dashboard',
            'original-policy' => self::POLICY,
            'disposition' => 'report',
        ], $override);

        return json_encode(['csp-report' => array_filter($report, fn($value) => $value !== null)]);
    }

    private function reportingApiEntry(array $override = []): array
    {
        return [
            'type' => 'csp-violation',
            'age' => 10,
            'url' => 'https://example.com/dashboard',
            'user_agent' => 'Test',
            'body' => array_merge([
                'documentURL' => 'https://example.com/dashboard',
                'effectiveDirective' => 'script-src-elem',
                'blockedURL' => 'https://cdn.example.org/y.js',
                'sourceFile' => 'https://example.com/dashboard',
                'originalPolicy' => self::POLICY,
                'disposition' => 'report',
            ], $override),
        ];
    }
}
