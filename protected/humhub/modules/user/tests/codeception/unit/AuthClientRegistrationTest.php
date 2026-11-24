<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\tests\codeception\unit;

use humhub\modules\user\controllers\AuthController;
use humhub\modules\user\models\User;
use humhub\modules\user\services\PendingAuthService;
use humhub\modules\user\source\GenericUserSource;
use tests\codeception\_support\HumHubDbTestCase;
use Yii;
use yii\authclient\BaseClient;
use yii\helpers\Url;

/**
 * Registration via an auth client of a user source if the user could not be created automatically.
 */
class AuthClientRegistrationTest extends HumHubDbTestCase
{
    private const CLIENT_ID = 'test-client';

    protected function setUp(): void
    {
        parent::setUp();

        Yii::$app->userSourceCollection->setUserSource('test-source', new class ([
            'id' => 'test-source',
            'allowedAuthClientIds' => [self::CLIENT_ID],
        ]) extends GenericUserSource {
            public function createUser(array $attributes): ?User
            {
                return null;
            }
        });
    }

    private function authenticate(): string
    {
        $client = new class extends BaseClient {
            protected function initUserAttributes()
            {
                return [];
            }
        };
        $client->setId(self::CLIENT_ID);
        $client->setUserAttributes(['id' => 'new-user', 'email' => 'new-user@example.com']);

        $controller = new AuthController('auth', Yii::$app->getModule('user'));

        return $controller->onAuthSuccess($client)->headers->get('location');
    }

    public function testErrorIsShownIfRegistrationFormIsHidden(): void
    {
        Yii::$app->getModule('user')->settings->set('auth.anonymousRegistration', false);

        $this->assertStringEndsWith(Url::to(['/user/auth/login']), $this->authenticate());
        $this->assertSame('Your account could not be created. Please contact the administrator.', Yii::$app->session->getFlash('error'));
        $this->assertFalse((new PendingAuthService())->hasPending());
    }

    public function testRegistrationFormIsShownIfVisible(): void
    {
        Yii::$app->getModule('user')->settings->set('auth.anonymousRegistration', true);

        $this->assertStringEndsWith(Url::to(['/user/registration']), $this->authenticate());
        $this->assertTrue((new PendingAuthService())->hasPending());
    }
}
