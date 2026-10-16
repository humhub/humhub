<?php

/*
 * @link      https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license   https://www.humhub.com/licences
 */

namespace humhub\tests\codeception\unit\services;

use Codeception\Test\Unit;
use humhub\services\DocumentRootProbeService;
use humhub\services\DocumentRootService;
use Yii;
use yii\console\Request as ConsoleRequest;
use yii\helpers\Url;

/**
 * @since 1.20
 */
class DocumentRootProbeServiceTest extends Unit
{
    private mixed $baseUrlBackup = null;

    protected function setUp(): void
    {
        parent::setUp();
        Yii::$app->cache->delete(DocumentRootProbeService::CACHE_KEY);
        $this->baseUrlBackup = Yii::$app->params['fixed-settings']['base']['baseUrl'] ?? null;
    }

    protected function tearDown(): void
    {
        if ($this->baseUrlBackup === null) {
            unset(Yii::$app->params['fixed-settings']['base']['baseUrl']);
        } else {
            Yii::$app->params['fixed-settings']['base']['baseUrl'] = $this->baseUrlBackup;
        }
        parent::tearDown();
    }

    /**
     * @param array<string, array{status: int, content: string, contentType: string}|null> $responses keyed by URL
     * @param string[] $requested collects the requested URLs in order
     */
    private function probeService(array $responses, array &$requested = []): DocumentRootProbeService
    {
        return new DocumentRootProbeService(
            documentRoot: new DocumentRootService('/var/www/humhub'),
            baseUrl: 'https://example.com/public',
            fetch: function (string $url) use ($responses, &$requested) {
                $requested[] = $url;
                return $responses[$url] ?? null;
            },
        );
    }

    /**
     * @param string $contentType a served markdown file is not `text/html` - see below
     */
    private static function response(int $status, string $content, string $contentType = 'text/markdown'): array
    {
        return ['status' => $status, 'content' => $content, 'contentType' => $contentType];
    }

    public function testAServedInstallationRootIsExposed()
    {
        $service = $this->probeService([
            'https://example.com/README.md' => self::response(200, '# HumHub'),
        ]);

        $this->assertSame(DocumentRootProbeService::STATE_EXPOSED, $service->probe('https://example.com'));
    }

    public function testANotFoundInstallationRootIsSafe()
    {
        $service = $this->probeService([
            'https://example.com/README.md' => self::response(404, 'Not Found', 'text/html'),
            'https://example.com/CHANGELOG.md' => self::response(404, 'Not Found', 'text/html'),
        ]);

        $this->assertSame(DocumentRootProbeService::STATE_SAFE, $service->probe('https://example.com'));
    }

    /**
     * Servers that answer unknown paths with a styled 200 page must not read as a hit.
     */
    public function testATwoHundredWithoutTheMarkerIsNotAHit()
    {
        $service = $this->probeService([
            'https://example.com/README.md' => self::response(200, 'Nothing here', 'text/plain'),
            'https://example.com/CHANGELOG.md' => self::response(200, 'Nothing here', 'text/plain'),
        ]);

        $this->assertSame(DocumentRootProbeService::STATE_SAFE, $service->probe('https://example.com'));
    }

    /**
     * Distribution packages may strip the README, so a second known installation root file is tried.
     */
    public function testTheChangelogIsTriedWhenTheReadmeIsMissing()
    {
        $service = $this->probeService([
            'https://example.com/README.md' => self::response(404, '', 'text/html'),
            'https://example.com/CHANGELOG.md' => self::response(200, "1.20.0\nHumHub"),
        ]);

        $this->assertSame(DocumentRootProbeService::STATE_EXPOSED, $service->probe('https://example.com'));
    }

    public function testAHitStopsFurtherRequests()
    {
        $requested = [];
        $service = $this->probeService([
            'https://example.com/README.md' => self::response(200, 'HumHub'),
        ], $requested);

        $service->probe('https://example.com');

        $this->assertSame(['https://example.com/README.md'], $requested);
    }

    public function testAnUnreachableSiteIsUnknownRatherThanSafe()
    {
        $service = $this->probeService([]);

        $this->assertSame(DocumentRootProbeService::STATE_UNKNOWN, $service->probe('https://example.com'));
    }

    /**
     * A site that cannot be reached for one file cannot be reached for the next either. Asking
     * anyway only costs a second timeout - on a single-worker setup, where the installation cannot
     * answer itself while it is busy asking, that doubles the wait on the administration page.
     */
    public function testAnUnreachableSiteStopsAfterTheFirstProbe()
    {
        $requested = [];
        $service = $this->probeService([
            'https://example.com/CHANGELOG.md' => self::response(404, '', 'text/html'),
        ], $requested);

        $this->assertSame(DocumentRootProbeService::STATE_UNKNOWN, $service->probe('https://example.com'));
        $this->assertSame(['https://example.com/README.md'], $requested);
    }

    /**
     * Where the `.htaccess` in the installation directory maps every request into `public/`, asking
     * for a file there reaches the application instead - which answers `text/html` and, being
     * HumHub, mentions HumHub. Only a response that is not an HTML document carries the file.
     */
    public function testTheApplicationAnsweringInsteadOfTheFileIsNotAHit()
    {
        $service = $this->probeService([
            'https://example.com/README.md' => self::response(200, '<html>HumHub</html>', 'text/html; charset=UTF-8'),
            'https://example.com/CHANGELOG.md' => self::response(200, '<html>HumHub</html>', 'text/html; charset=UTF-8'),
        ]);

        $this->assertSame(DocumentRootProbeService::STATE_SAFE, $service->probe('https://example.com'));
    }

    /**
     * If the deprecated entry script is served from the installation root, the root is exposed by
     * definition and there is nothing to ask the web server.
     */
    public function testTheLegacyEntryScriptIsExposedWithoutARequest()
    {
        $requested = [];
        $service = new DocumentRootProbeService(
            documentRoot: new DocumentRootService('/var/www/humhub', legacyEntryScriptPath: '/var/www/humhub'),
            baseUrl: 'https://example.com',
            fetch: function (string $url) use (&$requested) {
                $requested[] = $url;
                return null;
            },
        );

        $this->assertSame(DocumentRootProbeService::STATE_EXPOSED, $service->getState());
        $this->assertSame([], $requested);
    }

    public function testAnUnaddressableInstallationRootIsSafeWithoutARequest()
    {
        $requested = [];
        $service = new DocumentRootProbeService(
            documentRoot: new DocumentRootService('/var/www/humhub'),
            baseUrl: 'https://example.com',
            fetch: function (string $url) use (&$requested) {
                $requested[] = $url;
                return null;
            },
        );

        $this->assertSame(DocumentRootProbeService::STATE_SAFE, $service->getState());
        $this->assertSame([], $requested);
    }

    public function testTheVerdictIsCachedAcrossCalls()
    {
        $requested = [];
        $service = $this->probeService([
            'https://example.com/README.md' => self::response(200, 'HumHub'),
        ], $requested);

        $service->getState();
        $service->getState();

        $this->assertSame(['https://example.com/README.md'], $requested);
    }

    /**
     * The check asks how *this* request reached the application. The stored `baseUrl` setting can
     * predate a move of the document root, and trusting it makes the very misconfiguration the
     * check looks for invisible: reaching the app under `/public/index.php` while the setting still
     * says `https://example.com` derives no installation root URL and reports a clean bill.
     */
    public function testTheBaseUrlComesFromTheRequestAndNotFromTheStoredSetting()
    {
        $this->setConfiguredBaseUrl('https://stale.example.com');

        $this->assertSame(rtrim(Url::base(true), '/'), DocumentRootProbeService::detectBaseUrl());
        $this->assertNotSame('https://stale.example.com', DocumentRootProbeService::detectBaseUrl());
    }

    /**
     * Without a request nothing can be concluded, and concluding anything would be harmful: a
     * console run would write its guess into the same cache the web request reads, masking a real
     * finding for an hour.
     */
    public function testOutsideAWebRequestNothingIsConcluded()
    {
        $this->setConfiguredBaseUrl('https://cron.example.com');
        $webRequest = Yii::$app->get('request');
        Yii::$app->set('request', new ConsoleRequest());

        try {
            $this->assertNull(DocumentRootProbeService::detectBaseUrl());

            $requested = [];
            $service = new DocumentRootProbeService(
                documentRoot: new DocumentRootService('/var/www/humhub'),
                fetch: function (string $url) use (&$requested) {
                    $requested[] = $url;
                    return null;
                },
            );

            $this->assertSame(DocumentRootProbeService::STATE_UNKNOWN, $service->getState());
            $this->assertSame([], $requested);
            $this->assertFalse(Yii::$app->cache->get(DocumentRootProbeService::CACHE_KEY));
        } finally {
            Yii::$app->set('request', $webRequest);
        }
    }

    /**
     * The test configuration pins `baseUrl` as a fixed setting, so it is written through the param
     * it is fixed by rather than through the settings manager.
     */
    private function setConfiguredBaseUrl(string $baseUrl): void
    {
        Yii::$app->params['fixed-settings']['base']['baseUrl'] = $baseUrl;
    }

    /**
     * An unreachable site must not freeze its verdict for an hour - the next look should try again.
     * One probe per attempt, since the first failure ends it.
     */
    public function testAnUnknownVerdictIsNotCached()
    {
        $requested = [];
        $service = $this->probeService([], $requested);

        $service->getState();
        $service->getState();

        $this->assertSame(['https://example.com/README.md', 'https://example.com/README.md'], $requested);
    }
}
