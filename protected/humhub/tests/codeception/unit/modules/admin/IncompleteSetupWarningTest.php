<?php

/*
 * @link      https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license   https://www.humhub.com/licences
 */

namespace humhub\tests\codeception\unit\modules\admin;

use Codeception\Test\Unit;
use humhub\modules\admin\widgets\IncompleteSetupWarning;
use humhub\services\DocumentRootProbeService;
use humhub\services\DocumentRootService;
use Yii;

/**
 * @since 1.20
 */
class IncompleteSetupWarningTest extends Unit
{
    protected function setUp(): void
    {
        parent::setUp();
        Yii::$app->cache->delete(DocumentRootProbeService::CACHE_KEY);
    }

    private function probe(DocumentRootService $documentRoot, string $baseUrl, bool $served): DocumentRootProbeService
    {
        return new DocumentRootProbeService(
            documentRoot: $documentRoot,
            baseUrl: $baseUrl,
            fetch: fn(string $url) => $served
                ? ['status' => 200, 'content' => '# HumHub', 'contentType' => 'text/markdown']
                : ['status' => 404, 'content' => '', 'contentType' => 'text/html'],
        );
    }

    public function testAProperlyServedDocumentRootIsNoProblem()
    {
        $documentRoot = new DocumentRootService('/var/www/humhub');

        $this->assertSame([], IncompleteSetupWarning::getDocumentRootProblems(
            $documentRoot,
            $this->probe($documentRoot, 'https://example.com', false),
        ));
    }

    public function testAServedInstallationRootIsReported()
    {
        $documentRoot = new DocumentRootService('/var/www/humhub');

        $this->assertSame(
            [IncompleteSetupWarning::PROBLEM_DOCUMENT_ROOT_EXPOSED],
            IncompleteSetupWarning::getDocumentRootProblems(
                $documentRoot,
                $this->probe($documentRoot, 'https://example.com/public', true),
            ),
        );
    }

    /**
     * The deprecated entry script means the installation root is served, so both checks fire. The
     * dashboard gets the one that names the actual cause - two bullets asking for the same move
     * would only be noise.
     */
    public function testTheDeprecatedEntryScriptIsReportedOnItsOwn()
    {
        $documentRoot = new DocumentRootService('/var/www/humhub', legacyEntryScriptPath: '/var/www/humhub');

        $this->assertSame(
            [IncompleteSetupWarning::PROBLEM_LEGACY_ENTRY_SCRIPT],
            IncompleteSetupWarning::getDocumentRootProblems(
                $documentRoot,
                $this->probe($documentRoot, 'https://example.com', true),
            ),
        );
    }

    /**
     * An installation that cannot reach itself is a verification gap, not something to put a red
     * panel on the dashboard for. Prerequisites reports it.
     */
    public function testAnUnverifiableDocumentRootIsNoProblem()
    {
        $documentRoot = new DocumentRootService('/var/www/humhub');
        $probe = new DocumentRootProbeService(
            documentRoot: $documentRoot,
            baseUrl: 'https://example.com/public',
            fetch: fn(string $url) => null,
        );

        $this->assertSame([], IncompleteSetupWarning::getDocumentRootProblems($documentRoot, $probe));
    }
}
