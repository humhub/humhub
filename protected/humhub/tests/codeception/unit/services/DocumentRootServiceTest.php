<?php

/*
 * @link      https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license   https://www.humhub.com/licences
 */

namespace humhub\tests\codeception\unit\services;

use Codeception\Test\Unit;
use humhub\services\DocumentRootService;
use Yii;

/**
 * @since 1.20
 */
class DocumentRootServiceTest extends Unit
{
    private array $envBackup = [];

    protected function setUp(): void
    {
        parent::setUp();
        foreach ([DocumentRootService::ENV_LEGACY_ENTRY_SCRIPT, DocumentRootService::ENV_PUBLIC_URL] as $name) {
            $this->envBackup[$name] = $_ENV[$name] ?? null;
            unset($_ENV[$name]);
        }
    }

    protected function tearDown(): void
    {
        foreach ($this->envBackup as $name => $value) {
            if ($value === null) {
                unset($_ENV[$name]);
            } else {
                $_ENV[$name] = $value;
            }
        }
        parent::tearDown();
    }

    public function testPublicDirectorySitsBelowTheInstallationRoot()
    {
        $service = new DocumentRootService('/var/www/humhub');

        $this->assertSame('/var/www/humhub/public', $service->getPublicPath());
    }

    public function testTrailingSeparatorsOnTheInstallationRootAreIgnored()
    {
        $service = new DocumentRootService('/var/www/humhub/');

        $this->assertSame('/var/www/humhub', $service->getRootPath());
        $this->assertSame('/var/www/humhub/public', $service->getPublicPath());
    }

    public function testWithoutALegacyEntryScriptTheInstallationRunsOnTheDocumentRoot()
    {
        $service = new DocumentRootService('/var/www/humhub');

        $this->assertFalse($service->isLegacyEntryScript());
        $this->assertNull($service->getLegacyEntryScriptPath());
    }

    public function testALegacyEntryScriptIsReportedWithItsDirectory()
    {
        $service = new DocumentRootService('/var/www/humhub', legacyEntryScriptPath: '/var/www/humhub');

        $this->assertTrue($service->isLegacyEntryScript());
        $this->assertSame('/var/www/humhub', $service->getLegacyEntryScriptPath());
    }

    public function testOnTheDocumentRootTheWebUrlIsTheRequestBaseUrl()
    {
        $service = new DocumentRootService('/var/www/humhub');

        $this->assertSame('', $service->getWebUrl(''));
        $this->assertSame('/humhub', $service->getWebUrl('/humhub'));
    }

    public function testTheLegacyEntryScriptPrefixesTheWebUrlWithTheDocumentRootDirectory()
    {
        $service = new DocumentRootService('/var/www/humhub', legacyEntryScriptPath: '/var/www/humhub');

        $this->assertSame('/public', $service->getWebUrl(''));
        $this->assertSame('/humhub/public', $service->getWebUrl('/humhub'));
    }

    /**
     * The prefix is derived from the real paths rather than assembled from the directory name, so a
     * document root that is not a direct child of the legacy entry script still resolves.
     */
    public function testTheWebUrlPrefixSpansEveryLevelBetweenEntryScriptAndDocumentRoot()
    {
        $service = new DocumentRootService(
            '/var/www/humhub',
            publicPath: '/var/www/humhub/htdocs/public',
            legacyEntryScriptPath: '/var/www/humhub',
        );

        $this->assertSame('/htdocs/public', $service->getWebUrl(''));
    }

    public function testTheConfiguredPublicUrlWinsOverTheDerivedPrefix()
    {
        $service = new DocumentRootService(
            '/var/www/humhub',
            legacyEntryScriptPath: '/var/www/humhub',
            publicUrlOverride: '/assets-host',
        );

        $this->assertSame('/assets-host', $service->getWebUrl('/humhub'));
        $this->assertFalse($service->hasUnresolvableWebUrl());
    }

    public function testTheConfiguredPublicUrlAlsoAppliesWithoutALegacyEntryScript()
    {
        $service = new DocumentRootService('/var/www/humhub', publicUrlOverride: 'https://cdn.example.com');

        $this->assertSame('https://cdn.example.com', $service->getWebUrl(''));
    }

    /**
     * A hosting setup whose document root is not below the entry script cannot have its URL derived.
     * That has to surface as a check rather than as silently broken asset URLs.
     */
    public function testADocumentRootOutsideTheLegacyEntryScriptCannotBeDerived()
    {
        $service = new DocumentRootService(
            '/var/www/humhub',
            publicPath: '/srv/docroot',
            legacyEntryScriptPath: '/var/www/humhub',
        );

        $this->assertTrue($service->hasUnresolvableWebUrl());
        $this->assertSame('/humhub', $service->getWebUrl('/humhub'));
    }

    public function testWithoutALegacyEntryScriptTheWebUrlIsAlwaysResolvable()
    {
        $service = new DocumentRootService('/var/www/humhub', publicPath: '/srv/docroot');

        $this->assertFalse($service->hasUnresolvableWebUrl());
    }

    /**
     * A base URL ending in the document root's directory name means the web server hands out the
     * level above it - that level is the installation root.
     */
    public function testAnInstallationRootUrlIsDerivedFromABaseUrlEndingInTheDocumentRoot()
    {
        $service = new DocumentRootService('/var/www/humhub');

        $this->assertSame('https://example.com', $service->getInstallationRootUrl('https://example.com/public'));
        $this->assertSame('https://example.com/humhub', $service->getInstallationRootUrl('https://example.com/humhub/public/'));
    }

    public function testAProperlyServedDocumentRootExposesNoInstallationRootUrl()
    {
        $service = new DocumentRootService('/var/www/humhub');

        $this->assertNull($service->getInstallationRootUrl('https://example.com'));
        $this->assertNull($service->getInstallationRootUrl('https://example.com/humhub'));
    }

    /**
     * A path segment that merely ends in the directory name is a different directory.
     */
    public function testASegmentMerelyEndingInTheDocumentRootNameIsNotAMatch()
    {
        $service = new DocumentRootService('/var/www/humhub');

        $this->assertNull($service->getInstallationRootUrl('https://example.com/not-public'));
    }

    /**
     * The deprecated entry script is served from the installation root, so the base URL already is
     * the installation root's URL - no derivation involved.
     */
    public function testTheLegacyEntryScriptPutsTheInstallationRootOnTheBaseUrl()
    {
        $service = new DocumentRootService('/var/www/humhub', legacyEntryScriptPath: '/var/www/humhub');

        $this->assertSame('https://example.com', $service->getInstallationRootUrl('https://example.com'));
        $this->assertSame('https://example.com/humhub', $service->getInstallationRootUrl('https://example.com/humhub'));
    }

    public function testTheInstanceIsBuiltFromTheInstallationRootAlias()
    {
        $service = DocumentRootService::instance();

        $this->assertSame(rtrim(Yii::getAlias('@root'), '/'), $service->getRootPath());
        $this->assertFalse($service->isLegacyEntryScript());
    }

    public function testTheInstancePicksUpTheLegacyEntryScriptMarker()
    {
        $_ENV[DocumentRootService::ENV_LEGACY_ENTRY_SCRIPT] = '/var/www/humhub';

        $service = DocumentRootService::instance();

        $this->assertTrue($service->isLegacyEntryScript());
        $this->assertSame('/var/www/humhub', $service->getLegacyEntryScriptPath());
    }

    public function testTheInstancePicksUpTheConfiguredPublicUrl()
    {
        $_ENV[DocumentRootService::ENV_PUBLIC_URL] = '/docroot';

        $this->assertSame('/docroot', DocumentRootService::instance()->getWebUrl(''));
    }

    /**
     * An empty marker is an unset marker - `HUMHUB_LEGACY_ENTRY_SCRIPT=` in a `.env` file must not
     * put the installation into legacy mode.
     */
    public function testAnEmptyMarkerDoesNotEnableLegacyMode()
    {
        $_ENV[DocumentRootService::ENV_LEGACY_ENTRY_SCRIPT] = '';

        $this->assertFalse(DocumentRootService::instance()->isLegacyEntryScript());
    }
}
