<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\oauth\tests\codeception\unit;

use humhub\modules\oauth\components\ScopeRegistry;
use humhub\modules\oauth\models\Client;
use humhub\modules\oauth\models\forms\ClientForm;
use humhub\modules\user\models\User;
use tests\codeception\_support\HumHubDbTestCase;

class ClientFormTest extends HumHubDbTestCase
{
    private function form(array $attributes = [], ?Client $client = null, int $actorId = 1): ClientForm
    {
        $form = new ClientForm($client ?? new Client(), User::findOne(['id' => $actorId]), ['scenario' => ClientForm::SCENARIO_ADMIN]);
        $form->setAttributes($attributes + [
            'name' => 'My app',
            'redirectUris' => 'https://example.org/cb',
            'scopes' => ['content:read'],
        ]);

        return $form;
    }

    /**
     * @dataProvider redirectUriProvider
     */
    public function testRedirectUris(string $uris, bool $confidential, bool $valid)
    {
        $form = $this->form(['redirectUris' => $uris, 'confidential' => $confidential]);
        $this->assertSame($valid, $form->validate(), implode(', ', $form->getErrorSummary(true)));
    }

    public static function redirectUriProvider(): array
    {
        return [
            'https' => ['https://example.org/cb', false, true],
            'loopback v4' => ['http://127.0.0.1/cb', true, true],
            'loopback v4 with port' => ['http://127.0.0.1:8123/cb', true, true],
            'loopback v6' => ['http://[::1]/cb', true, true],
            'private-use scheme, public client' => ['com.example.app:/cb', false, true],
            'private-use scheme, confidential client' => ['com.example.app:/cb', true, false],
            'plain http' => ['http://example.org/cb', false, false],
            'loopback lookalike' => ['http://127.0.0.1.example.org/cb', false, false],
            'javascript' => ['javascript:alert(1)', false, false],
            'fragment' => ['https://example.org/cb#x', false, false],
            'several, one bad' => ["https://example.org/cb\nhttp://example.org/cb", false, false],
            'several' => ["https://example.org/cb\r\n\r\nhttp://127.0.0.1/cb", false, true],
            'wildcard host' => ['https://*.example.org/cb', false, false],
            'userinfo' => ['https://user:pw@example.org/cb', false, false],
            'user only' => ['https://user@example.org/cb', false, false],
            'space in host' => ['https://exa mple.org/cb', false, false],
            'space in path' => ['https://example.org/c b', false, false],
            'control character' => ["https://example.org/c\x01b", false, false],
            'no host' => ['https:/cb', false, false],
            'ip host' => ['https://192.0.2.1/cb', false, true],
            'upper-case scheme' => ['HTTPS://Example.org/cb', false, true],
            'empty' => ['', false, false],
            'whitespace only' => ["  \n ", false, false],
        ];
    }

    public function testSchemeIsStoredInLowerCase()
    {
        $form = $this->form(['redirectUris' => "HTTPS://Example.org/cb\nCom.Example.App:/cb"]);
        $this->assertTrue($form->save());

        $this->assertSame(['https://Example.org/cb', 'com.example.app:/cb'], Client::findOne(['id' => $form->client->id])->getRedirectUris());
    }

    public function testAdminScenarioOffersAllScopes()
    {
        $form = $this->form(['scopes' => ['full']]);

        $this->assertSame(array_keys(ScopeRegistry::getScopes()), array_keys($form->getScopeOptions()));
        $this->assertTrue($form->validate());
    }

    public function testConfidentialClientGetsASecretOnce()
    {
        $form = $this->form(['confidential' => true]);
        $this->assertTrue($form->save());

        $secret = $form->getSecret();
        $this->assertNotNull($secret);
        $this->assertGreaterThanOrEqual(40, strlen($secret));

        $client = Client::findOne(['id' => $form->client->id]);
        $this->assertSame(hash('sha256', $secret), $client->secret_hash);
        $this->assertTrue($client->validateSecret($secret));

        // Editing keeps the secret and shows none
        $edit = new ClientForm($client, User::findOne(['id' => 1]), ['scenario' => ClientForm::SCENARIO_ADMIN]);
        $edit->name = 'Renamed';
        $this->assertTrue($edit->save());
        $this->assertNull($edit->getSecret());
        $this->assertTrue(Client::findOne(['id' => $client->id])->validateSecret($secret));
    }

    public function testRegeneratingTheSecretChangesTheHash()
    {
        $form = $this->form(['confidential' => true]);
        $form->save();
        $oldHash = $form->client->secret_hash;

        $form->generateSecret();
        $form->client->save(false);

        $client = Client::findOne(['id' => $form->client->id]);
        $this->assertNotSame($oldHash, $client->secret_hash);
        $this->assertTrue($client->validateSecret($form->getSecret()));
    }

    public function testMakingAClientPublicDropsTheSecret()
    {
        $form = $this->form(['confidential' => true]);
        $form->save();

        $edit = new ClientForm($form->client, User::findOne(['id' => 1]), ['scenario' => ClientForm::SCENARIO_ADMIN]);
        $edit->confidential = false;
        $this->assertTrue($edit->save());
        $this->assertNull(Client::findOne(['id' => $form->client->id])->secret_hash);
    }

    public function testAdminClient()
    {
        $form = $this->form(['skipConsent' => true, 'isActive' => false]);
        $this->assertTrue($form->save());

        $client = Client::findOne(['id' => $form->client->id]);
        $this->assertMatchesRegularExpression('/^[0-9a-f]{32}$/', $client->client_id);
        $this->assertSame(Client::TYPE_ADMIN, $client->type);
        $this->assertSame(['https://example.org/cb'], $client->getRedirectUris());
        $this->assertSame(['content:read'], $client->getAllowedScopes());
        $this->assertFalse($client->isConfidential());
        $this->assertNull($form->getSecret());
        $this->assertSame(1, (int)$client->created_by);
        $this->assertSame(1, (int)$client->skip_consent);
        $this->assertSame(0, (int)$client->is_active);
    }

    public function testFullIsStoredAlone()
    {
        $form = $this->form(['scopes' => ['content:read', 'full', 'spaces:read']]);
        $this->assertTrue($form->save());

        $this->assertSame(['full'], $form->scopes);
        $this->assertSame(['full'], Client::findOne(['id' => $form->client->id])->getAllowedScopes());
    }

    public function testScopesNoLongerRegisteredAreDroppedOnEdit()
    {
        $form = $this->form();
        $this->assertTrue($form->save());
        $form->client->updateAttributes(['scopes' => json_encode(['content:read', 'gone:read'])]);

        $edit = new ClientForm(Client::findOne(['id' => $form->client->id]), User::findOne(['id' => 1]), ['scenario' => ClientForm::SCENARIO_ADMIN]);
        $this->assertSame(['content:read'], $edit->scopes);
        $this->assertTrue($edit->save());
    }

    public function testOnlySystemAdminsSetFullAndSkipConsent()
    {
        // User 2 is no system administrator, e.g. a holder of "Manage settings"
        $form = $this->form(['scopes' => ['full']], actorId: 2);
        $this->assertFalse($form->canSetPrivileged());
        $this->assertArrayNotHasKey(ScopeRegistry::FULL, $form->getScopeOptions());
        $this->assertFalse($form->validate());
        $this->assertTrue($form->hasErrors('scopes'));

        $form = $this->form(['skipConsent' => true], actorId: 2);
        $this->assertFalse($form->validate());
        $this->assertTrue($form->hasErrors('skipConsent'));

        $form = $this->form(['scopes' => ['full'], 'skipConsent' => true]);
        $this->assertTrue($form->canSetPrivileged());
        $this->assertTrue($form->validate());
    }

    public function testPrivilegedValuesStayWhenOthersEditTheClient()
    {
        $form = $this->form(['scopes' => ['full'], 'skipConsent' => true]);
        $this->assertTrue($form->save());

        $edit = new ClientForm(Client::findOne(['id' => $form->client->id]), User::findOne(['id' => 2]), ['scenario' => ClientForm::SCENARIO_ADMIN]);
        $this->assertTrue($edit->hasLockedFullAccess());
        $this->assertSame([], $edit->scopes);
        // No scope has to be chosen, and none can replace `full`
        $edit->setAttributes(['name' => 'Renamed', 'scopes' => ['spaces:read']]);
        $this->assertTrue($edit->save(), implode(', ', $edit->getErrorSummary(true)));

        $client = Client::findOne(['id' => $form->client->id]);
        $this->assertSame('Renamed', $client->name);
        $this->assertSame(['full'], $client->getAllowedScopes());
        $this->assertSame(1, (int)$client->skip_consent);
    }
}
