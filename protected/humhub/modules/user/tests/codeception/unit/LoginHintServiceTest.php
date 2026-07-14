<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\tests\codeception\unit;

use humhub\modules\user\services\LoginHintService;
use tests\codeception\_support\HumHubDbTestCase;

/**
 * Covers the session round-trip of the Step-1 login hint that external
 * auth clients (SAML, OIDC, …) forward to their IdP.
 *
 * @since 1.19
 */
class LoginHintServiceTest extends HumHubDbTestCase
{
    public function testConsumeReturnsNullWhenNothingIsSet(): void
    {
        $this->assertNull((new LoginHintService())->consume());
    }

    public function testConsumeReturnsHintExactlyOnce(): void
    {
        $service = new LoginHintService();
        $service->set('user@example.com');

        $this->assertSame('user@example.com', $service->consume());
        $this->assertNull($service->consume());
    }

    public function testEmptyHintIsNotStored(): void
    {
        $service = new LoginHintService();
        $service->set('');

        $this->assertNull($service->consume());
    }

    public function testClearRemovesHint(): void
    {
        $service = new LoginHintService();
        $service->set('user@example.com');
        $service->clear();

        $this->assertNull($service->consume());
    }
}
