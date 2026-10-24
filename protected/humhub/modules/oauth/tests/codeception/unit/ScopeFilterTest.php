<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\oauth\tests\codeception\unit;

use humhub\modules\oauth\components\ScopeFilter;
use humhub\modules\oauth\models\Client;
use humhub\modules\oauth\models\Grant;
use humhub\modules\oauth\Module;
use humhub\modules\oauth\services\GrantService;
use humhub\modules\user\models\User;
use tests\codeception\_support\HumHubDbTestCase;
use Yii;
use yii\base\Action;

class ScopeFilterTest extends HumHubDbTestCase
{
    protected function tearDown(): void
    {
        Module::instance()->activeGrant = null;
        parent::tearDown();
    }

    private function grant(array $scopes): Grant
    {
        $user = User::findOne(['id' => 1]);
        if ($scopes === ['full']) {
            return (new GrantService())->createOAuthGrant($user, Client::findByClientId(Client::SYSTEM_CLIENT_ID), $scopes);
        }

        Module::instance()->settings->set('allowClients', true);
        $client = new Client([
            'client_id' => bin2hex(random_bytes(16)), 'type' => Client::TYPE_ADMIN, 'name' => 'Test',
            'redirect_uris' => json_encode(['https://example.org/cb']),
            'scopes' => json_encode($scopes), 'is_active' => 1, 'created_at' => time(),
        ]);
        $this->assertTrue($client->save(false));

        return (new GrantService())->createOAuthGrant($user, $client, $scopes);
    }

    private function filter(array $scopes, bool $allowFull, string $actionId = 'index', bool $requireDeclaredScope = false): bool
    {
        Yii::$app->response->setStatusCode(200);
        Yii::$app->response->getHeaders()->remove('WWW-Authenticate');
        $controller = Yii::$app->createController('oauth/metadata')[0];
        $filter = new ScopeFilter(['scopes' => $scopes, 'allowFull' => $allowFull, 'requireDeclaredScope' => $requireDeclaredScope]);

        return $filter->beforeAction(new Action($actionId, $controller));
    }

    public function testRequestWithoutGrantPasses()
    {
        Module::instance()->activeGrant = null;
        $this->assertTrue($this->filter([], false));
    }

    public function testFullPassesOnlyWithAllowFull()
    {
        Module::instance()->activeGrant = $this->grant(['full']);

        $this->assertTrue($this->filter(['*' => 'content:read'], true));

        $this->assertFalse($this->filter(['*' => 'content:read'], false));
        $this->assertSame(403, Yii::$app->response->statusCode);
        $this->assertSame(['error' => 'insufficient_scope', 'scope' => 'content:read'], Yii::$app->response->data);
    }

    public function testAnyScopeOfAListSuffices()
    {
        Module::instance()->activeGrant = $this->grant(['content:write']);

        $this->assertTrue($this->filter(['index' => ['content:read', 'content:write']], false));
        $this->assertFalse($this->filter(['index' => ['content:read', 'spaces:read']], false));
        $this->assertSame(['error' => 'insufficient_scope', 'scope' => 'content:read spaces:read'], Yii::$app->response->data);
    }

    public function testActionWithoutEntryNeedsFull()
    {
        $scopes = ['view' => 'content:read'];

        Module::instance()->activeGrant = $this->grant(['content:read']);
        $this->assertFalse($this->filter($scopes, true));
        $this->assertSame(['error' => 'insufficient_scope', 'scope' => 'full'], Yii::$app->response->data);

        Module::instance()->activeGrant = $this->grant(['full']);
        $this->assertTrue($this->filter($scopes, true));
    }

    public function testRequireDeclaredScopeRefusesUndeclaredActionEvenWithFull()
    {
        $scopes = ['view' => 'content:read'];

        Module::instance()->activeGrant = $this->grant(['full']);
        $this->assertFalse($this->filter($scopes, true, 'index', true));
        $this->assertSame(403, Yii::$app->response->statusCode);
        $this->assertSame(['error' => 'insufficient_scope'], Yii::$app->response->data);
        $this->assertSame('Bearer error="insufficient_scope"', Yii::$app->response->getHeaders()->get('WWW-Authenticate'));

        $this->assertFalse($this->filter([], true, 'index', true));
    }

    public function testRequireDeclaredScopePassesDeclaredScopeOrFull()
    {
        $scopes = ['view' => 'content:read', '*' => 'content:write'];

        Module::instance()->activeGrant = $this->grant(['full']);
        $this->assertTrue($this->filter($scopes, true, 'view', true));
        $this->assertTrue($this->filter($scopes, true, 'index', true));
        $this->assertFalse($this->filter($scopes, false, 'view', true));
        $this->assertSame(['error' => 'insufficient_scope', 'scope' => 'content:read'], Yii::$app->response->data);

        Module::instance()->activeGrant = $this->grant(['content:read']);
        $this->assertTrue($this->filter($scopes, true, 'view', true));
        $this->assertFalse($this->filter($scopes, true, 'index', true));
        $this->assertSame(['error' => 'insufficient_scope', 'scope' => 'content:write'], Yii::$app->response->data);
    }

    public function testRequireDeclaredScopeLetsRequestsWithoutGrantPass()
    {
        Module::instance()->activeGrant = null;
        $this->assertTrue($this->filter([], false, 'index', true));
    }
}
