<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\tests\codeception\api;

use ApiTester;
use humhub\modules\oauth\models\Client;
use humhub\modules\oauth\models\Grant;
use humhub\modules\oauth\Module;
use humhub\modules\oauth\services\GrantService;
use humhub\modules\oauth\services\PersonalAccessTokenService;
use humhub\modules\user\models\User;
use PHPUnit\Framework\Assert;
use Yii;

/**
 * The OAuth clients and the grants of all users as rows of the administration's tables
 * (`humhub\modules\oauth\controllers\api\AdminClientController`, `AdminGrantController`:
 * `GET /api/v2/admin/oauth-client`, `/admin/oauth-grant`).
 *
 * `_before()` creates two clients with grants of users 2 and 3 and a token of user 2; `_after()`
 * removes them.
 */
class AdminOauthApiCest
{
    private Client $agent;
    private Client $other;

    public function _before(): void
    {
        Module::instance()->settings->set('allowClients', true);
        $this->agent = $this->client('agent', 'Research Agent');
        $this->other = $this->client('other', 'Other Tool');
        $service = new GrantService();
        $service->createOAuthGrant(User::findOne(['id' => 2]), $this->agent, ['content:read']);
        $service->createOAuthGrant(User::findOne(['id' => 3]), $this->agent, ['content:read']);
        $service->createOAuthGrant(User::findOne(['id' => 2]), $this->other, ['content:read']);
        (new PersonalAccessTokenService())->create(User::findOne(['id' => 2]), 'Script', ['content:read'], 30);
    }

    public function _after(): void
    {
        // Rows an API Cest writes outlive it
        Grant::deleteAll();
        Client::deleteAll(['client_id' => ['agent', 'other']]);
    }

    private function client(string $id, string $name): Client
    {
        $client = new Client([
            'client_id' => $id, 'type' => Client::TYPE_ADMIN, 'name' => $name,
            'redirect_uris' => json_encode(['https://example.org/cb']),
            'scopes' => json_encode(['content:read']), 'is_active' => 1, 'created_at' => time(),
        ]);
        $client->save(false);

        return $client;
    }

    private function withCsrf(ApiTester $I): void
    {
        $rawToken = Yii::$app->security->generateRandomString();
        $I->setCookie('_csrf', $rawToken);
        $I->haveHttpHeader('X-CSRF-Token', Yii::$app->security->maskToken($rawToken));
    }

    private function names(ApiTester $I): array
    {
        return $I->grabDataFromResponseByJsonPath('$.results[*].cells.name.text');
    }

    public function testListsTheClientsTheAppFirst(ApiTester $I)
    {
        $I->amLoggedInAs(1);
        $I->sendGet('admin/oauth-client');
        $I->seeResponseCodeIs(200);
        $names = $this->names($I);
        Assert::assertContains('Research Agent', $names);
        // The HumHub app offers no actions
        $system = Client::findByClientId(Client::SYSTEM_CLIENT_ID);
        $I->seeResponseContainsJson(['results' => [['id' => $system->id, 'actions' => []]]]);
    }

    public function testFiltersTheGrants(ApiTester $I)
    {
        $I->amLoggedInAs(1);

        $I->sendGet('admin/oauth-grant');
        $I->seeResponseContainsJson(['total' => 4]);

        $I->sendGet('admin/oauth-grant', ['q' => User::findOne(['id' => 3])->username]);
        Assert::assertSame(['Research Agent'], $this->names($I));

        $I->sendGet('admin/oauth-grant', ['client' => (string)$this->other->id]);
        Assert::assertSame(['Other Tool'], $this->names($I));

        $I->sendGet('admin/oauth-grant', ['type' => Grant::TYPE_PAT]);
        Assert::assertSame(['Script'], $this->names($I));

        // A scope: the grants with it, and those with full access
        (new GrantService())->createOAuthGrant(User::findOne(['id' => 3]), $this->agent, ['spaces:read']);
        $full = (new PersonalAccessTokenService())->create(User::findOne(['id' => 3]), 'Everything', ['full'], 30)[1];
        $I->sendGet('admin/oauth-grant', ['scope' => 'spaces:read']);
        Assert::assertEqualsCanonicalizing(['Research Agent', 'Everything'], $this->names($I));
        $I->sendGet('admin/oauth-grant', ['scope' => 'full']);
        Assert::assertSame([$full->id], $I->grabDataFromResponseByJsonPath('$.results[*].id'));
        $I->sendGet('admin/oauth-grant', ['scope' => 'unknown']);
        $I->seeResponseCodeIs(422);

        $I->sendGet('admin/oauth-grant', ['type' => 'bogus']);
        $I->seeResponseCodeIs(422);
        $I->sendGet('admin/oauth-grant', ['type' => ['oauth'], 'q' => ['x']]);
        $I->seeResponseCodeIs(422);

        // The scopes by their labels
        $I->sendGet('admin/oauth-grant', ['client' => (string)$this->other->id]);
        Assert::assertSame(['Read content, comments and likes'], $I->grabDataFromResponseByJsonPath('$.results[0].cells.scopes[*].text'));
    }

    public function testRevokesAGrantAndAllOfAFilter(ApiTester $I)
    {
        $I->amLoggedInAs(1);
        $this->withCsrf($I);
        $keep = Grant::findOne(['client_id' => $this->other->id]);

        $grant = Grant::find()->where(['client_id' => $this->agent->id, 'user_id' => 3])->one();
        $I->sendPost('admin/oauth-grant/' . $grant->id . '/revoke');
        $I->seeResponseContainsJson(['removed' => true]);
        Assert::assertFalse(Grant::find()->where(['id' => $grant->id])->exists());

        // All of no filter: refused
        $I->sendPost('admin/oauth-grant/bulk/revoke', ['all' => 1]);
        $I->seeResponseCodeIs(422);
        Assert::assertSame(3, (int)Grant::find()->count());

        $I->sendPost('admin/oauth-grant/bulk/revoke?type=' . Grant::TYPE_OAUTH, ['all' => 1]);
        $I->seeResponseContainsJson(['done' => 2]);
        Assert::assertFalse(Grant::find()->where(['id' => $keep->id])->exists());
        Assert::assertSame([Grant::TYPE_PAT], Grant::find()->select('type')->column());
    }

    public function testRequiresTheSettings(ApiTester $I)
    {
        $I->amLoggedInAs(2);
        $I->sendGet('admin/oauth-grant');
        $I->seeResponseCodeIs(403);
        $I->sendGet('admin/oauth-client');
        $I->seeResponseCodeIs(403);
    }
}
