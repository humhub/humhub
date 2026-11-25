<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\components\health\checks;

use humhub\components\health\HealthCheck;
use humhub\components\health\HealthResult;
use humhub\services\DocumentRootProbeService;
use humhub\services\DocumentRootService;

/**
 * Whether the web server serves the installation root instead of the public directory.
 *
 * Both findings mean the installation root is served; only the one naming the actual cause is reported.
 * An installation that cannot reach itself is a verification gap, not a finding. A correctly served
 * installation costs no HTTP request (see DocumentRootProbeService), the verdict is cached there.
 *
 * @since 1.20
 */
class DocumentRootCheck extends HealthCheck
{
    public const FINDING_LEGACY_ENTRY_SCRIPT = 'legacy-entry-script';
    public const FINDING_DOCUMENT_ROOT_EXPOSED = 'document-root-exposed';

    public string $id = 'document-root';

    public ?DocumentRootService $documentRoot = null;

    public ?DocumentRootProbeService $probe = null;

    public function getFinding(): ?string
    {
        $documentRoot = $this->documentRoot ?? DocumentRootService::instance();
        if ($documentRoot->isLegacyEntryScript()) {
            return self::FINDING_LEGACY_ENTRY_SCRIPT;
        }

        $probe = $this->probe ?? DocumentRootProbeService::instance();

        return $probe->getState() === DocumentRootProbeService::STATE_EXPOSED
            ? self::FINDING_DOCUMENT_ROOT_EXPOSED
            : null;
    }

    public function run(): HealthResult
    {
        return match ($this->getFinding()) {
            self::FINDING_LEGACY_ENTRY_SCRIPT => HealthResult::warning('Served through the deprecated entry script in the installation root'),
            self::FINDING_DOCUMENT_ROOT_EXPOSED => HealthResult::failure('The installation root is served, the document root must be the public directory'),
            default => HealthResult::ok(),
        };
    }
}
