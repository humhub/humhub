<?php

/*
 * @link      https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license   https://www.humhub.com/licences
 */

namespace humhub\tests\codeception\unit\components;

use Codeception\Test\Unit;
use humhub\components\Request;
use humhub\services\DocumentRootService;

/**
 * @since 1.20
 */
class RequestTest extends Unit
{
    private function resolve(string $scriptUrl, string $requestUri): ?string
    {
        return Request::resolveRewrittenScriptUrl($scriptUrl, $requestUri, DocumentRootService::PUBLIC_DIR);
    }

    /**
     * The document root is the `public/` directory itself - the entry script is at the site root and
     * there is nothing to correct.
     */
    public function testAnEntryScriptAtTheSiteRootNeedsNoCorrection()
    {
        $this->assertNull($this->resolve('/index.php', '/dashboard'));
    }

    /**
     * The document root is the installation directory and the site is genuinely reached under
     * `/public` - the URL carries the segment, so it belongs in the base URL.
     */
    public function testAGenuinelyRequestedPublicSegmentIsKept()
    {
        $this->assertNull($this->resolve('/public/index.php', '/public/dashboard'));
        $this->assertNull($this->resolve('/public/index.php', '/public/index.php?r=dashboard'));
        $this->assertNull($this->resolve('/public/index.php', '/public'));
    }

    /**
     * The `.htaccess` in the installation directory maps every request into `public/`. Apache then
     * reports an entry script the visitor never asked for, and Yii would derive `/public` as the
     * base URL - which does not match the request and makes it fail to resolve the path info at all.
     */
    public function testARewrittenRequestDropsThePublicSegment()
    {
        $this->assertSame('/index.php', $this->resolve('/public/index.php', '/dashboard'));
        $this->assertSame('/index.php', $this->resolve('/public/index.php', '/'));
        $this->assertSame('/index.php', $this->resolve('/public/index.php', '/some/deep/route?x=1'));
    }

    public function testARewrittenRequestKeepsASubdirectoryInstallationsPrefix()
    {
        $this->assertSame('/humhub/index.php', $this->resolve('/humhub/public/index.php', '/humhub/dashboard'));
        $this->assertSame('/humhub/index.php', $this->resolve('/humhub/public/index.php', '/humhub/'));
    }

    /**
     * A subdirectory installation reached under its `/public` segment is not rewritten.
     */
    public function testASubdirectoryInstallationRequestedUnderPublicIsKept()
    {
        $this->assertNull($this->resolve('/humhub/public/index.php', '/humhub/public/dashboard'));
    }

    /**
     * The deprecated entry script in the installation directory is not below `public/`, so nothing
     * about it looks rewritten.
     */
    public function testTheDeprecatedEntryScriptNeedsNoCorrection()
    {
        $this->assertNull($this->resolve('/index.php', '/dashboard'));
        $this->assertNull($this->resolve('/humhub/index.php', '/humhub/dashboard'));
    }

    /**
     * A directory that merely ends in the same letters is a different directory.
     */
    public function testADirectoryMerelyEndingInTheDocumentRootNameIsNotAMatch()
    {
        $this->assertNull($this->resolve('/not-public/index.php', '/dashboard'));
    }
}
