<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\oauth\tests\codeception\unit;

use humhub\helpers\EnvHelper;
use humhub\modules\oauth\models\Client;
use humhub\modules\oauth\Module;
use humhub\modules\oauth\tests\codeception\_support\OAuthTestHelper;
use League\OAuth2\Server\Exception\OAuthServerException;
use tests\codeception\_support\HumHubDbTestCase;

class AppIdsTest extends HumHubDbTestCase
{
    protected function _before()
    {
        parent::_before();
        Module::instance()->getKeyStore()->generateIfMissing();
    }

    protected function _after()
    {
        Module::instance()->appIds = null;
        Module::instance()->settings->delete('appIds');
        parent::_after();
    }

    public function testTheBuiltInAppIdIsAlwaysIncluded()
    {
        $this->assertSame(['com.humhub.app'], Module::instance()->getAppIds());

        Module::instance()->settings->set('appIds', json_encode(['com.acme.app', 'com.humhub.app', 'invalid']));
        $this->assertSame(['com.humhub.app', 'com.acme.app'], Module::instance()->getAppIds());
        $this->assertSame(['com.acme.app'], Module::instance()->getAdditionalAppIds());
    }

    public function testTheConfigurationWins()
    {
        Module::instance()->settings->set('appIds', json_encode(['com.stored.app']));

        Module::instance()->appIds = ['Com.Configured.App'];
        $this->assertTrue(Module::instance()->isAppIdsConfigured());
        $this->assertSame(['com.humhub.app', 'com.configured.app'], Module::instance()->getAppIds());

        // A comma-separated string, as a plain environment variable gives it
        Module::instance()->appIds = 'com.a.app, com.b.app';
        $this->assertSame(['com.humhub.app', 'com.a.app', 'com.b.app'], Module::instance()->getAppIds());

        // An empty list locks it to the built-in app
        Module::instance()->appIds = [];
        $this->assertSame(['com.humhub.app'], Module::instance()->getAppIds());
    }

    public function testTheEnvironmentVariableSetsTheProperty()
    {
        $config = EnvHelper::toConfig(['HUMHUB_CONFIG__MODULES__OAUTH__APP_IDS' => '["com.acme.app","de.example.intranet"]']);
        $this->assertSame(['com.acme.app', 'de.example.intranet'], $config['modules']['oauth']['appIds']);

        $config = EnvHelper::toConfig(['HUMHUB_CONFIG__MODULES__OAUTH__APP_IDS' => 'com.acme.app']);
        $this->assertSame('com.acme.app', $config['modules']['oauth']['appIds']);
    }

    public function testValidation()
    {
        foreach (['com.acme.app', 'de.example-corp.intranet2', 'io.a1.b'] as $valid) {
            $this->assertTrue(Module::isValidAppId($valid), $valid);
        }
        foreach (['foo', 'com..acme', 'https://x', 'com.acme app', ' com.acme.app', 'com.acme.', '.com.acme', '1com.acme', 'com.my_company.app', 'Com.Acme.App', ''] as $invalid) {
            $this->assertFalse(Module::isValidAppId($invalid), $invalid);
        }
    }

    public function testTheSystemClientAcceptsTheRedirectUriOfEveryAppId()
    {
        Module::instance()->settings->set('appIds', json_encode(['com.acme.app']));
        $this->assertSame(
            ['com.humhub.app:/oauth/callback', 'com.acme.app:/oauth/callback'],
            Client::findByClientId(Client::SYSTEM_CLIENT_ID)->getRedirectUris(),
        );

        $verifier = str_repeat('v', 64);
        $this->assertNotEmpty(OAuthTestHelper::issueAuthorizationCode(1, $verifier, Client::SYSTEM_CLIENT_ID, 'com.acme.app:/oauth/callback'));
        $this->assertNotEmpty(OAuthTestHelper::issueAuthorizationCode(1, $verifier));

        $this->expectException(OAuthServerException::class);
        OAuthTestHelper::issueAuthorizationCode(1, $verifier, Client::SYSTEM_CLIENT_ID, 'com.other.app:/oauth/callback');
    }
}
