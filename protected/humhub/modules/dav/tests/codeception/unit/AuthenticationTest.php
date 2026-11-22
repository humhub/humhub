<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\dav\tests\codeception\unit;

use humhub\modules\dav\components\TokenBasicAuth;
use humhub\modules\dav\controllers\ServerController;
use humhub\modules\dav\Module;
use humhub\modules\oauth\models\Grant;
use humhub\modules\oauth\Module as OAuthModule;
use humhub\modules\oauth\services\PersonalAccessTokenService;
use humhub\modules\user\models\User;
use tests\codeception\_support\HumHubDbTestCase;
use Yii;
use yii\base\InlineAction;

/**
 * A request below `/dav/` authenticates with a personal access token as the password of
 * HTTP Basic, and with nothing else.
 */
class AuthenticationTest extends HumHubDbTestCase
{
    private string $token;
    private Grant $grant;

    protected function _before()
    {
        parent::_before();

        OAuthModule::instance()->settings->set('allowPersonalAccessTokens', true);
        [$this->token, $this->grant] = (new PersonalAccessTokenService())->create(User::findOne(['id' => 2]), 'Laptop', ['content:read'], 30);
    }

    protected function _after()
    {
        unset($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW']);
        OAuthModule::instance()->activeGrant = null;
        Grant::deleteAll();

        parent::_after();
    }

    /**
     * Runs the authentication of the endpoint with these credentials.
     */
    private function authenticate(?string $username, ?string $password): bool
    {
        unset($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW']);
        if ($username !== null) {
            $_SERVER['PHP_AUTH_USER'] = $username;
            $_SERVER['PHP_AUTH_PW'] = (string)$password;
        }
        Yii::$app->response->setStatusCode(200);
        Yii::$app->response->headers->removeAll();

        $controller = new ServerController('server', Module::instance());

        return $controller->beforeAction(new InlineAction('index', $controller, 'actionIndex'));
    }

    private function assertRefused(bool $passed): void
    {
        $this->assertFalse($passed);
        $this->assertSame(401, Yii::$app->response->statusCode);
        $this->assertStringStartsWith('Basic realm=', (string)Yii::$app->response->headers->get('WWW-Authenticate'));
        $this->assertNull(OAuthModule::instance()->activeGrant);
    }

    public function testATokenWithItsUsernameAuthenticatesWithoutASession()
    {
        $this->assertTrue($this->authenticate('User1', $this->token));

        $this->assertSame(2, (int)Yii::$app->user->id);
        $this->assertFalse(Yii::$app->user->enableSession);
        $this->assertSame((int)$this->grant->id, (int)OAuthModule::instance()->activeGrant->id);
    }

    public function testTheEmailAddressInAnyCaseIsAUsernameToo()
    {
        $email = User::findOne(['id' => 2])->email;

        $this->assertTrue($this->authenticate(strtoupper($email), $this->token));
    }

    public function testNoCredentialsAreAChallenge()
    {
        $this->assertRefused($this->authenticate(null, null));
    }

    public function testTheUsernameMustBeTheOwners()
    {
        $this->assertRefused($this->authenticate('Admin', $this->token));
    }

    public function testThePasswordOfTheAccountIsNoToken()
    {
        $this->assertRefused($this->authenticate('User1', '123qweasdQWE'));
    }

    public function testAnUnknownOrExpiredTokenIsRefused()
    {
        $this->assertRefused($this->authenticate('User1', $this->token . 'x'));

        $this->grant->updateAttributes(['expires_at' => time() - 1]);
        $this->assertRefused($this->authenticate('User1', $this->token));
    }

    public function testAnImportedPasswordAuthenticatesOnlyWhereItsIssuerAsksForIt()
    {
        $user = User::findOne(['id' => 2]);
        $grant = (new PersonalAccessTokenService())->import($user, 'test-calendar', 'Earlier password', ['content:read'], 'an-earlier-password', null);
        $_SERVER['PHP_AUTH_USER'] = $user->username;
        $_SERVER['PHP_AUTH_PW'] = 'an-earlier-password';

        $this->assertSame($grant->id, (new TokenBasicAuth())->authenticate(Yii::$app->request, 'test-calendar')?->id);
        $this->assertNull((new TokenBasicAuth())->authenticate(Yii::$app->request, 'other'));
        // Not at /dav/
        $this->assertRefused($this->authenticate($user->username, 'an-earlier-password'));
    }

    public function testNoTokenWorksWhileTokensAreNotAllowed()
    {
        OAuthModule::instance()->settings->set('allowPersonalAccessTokens', false);

        $this->assertRefused($this->authenticate('User1', $this->token));
    }
}
