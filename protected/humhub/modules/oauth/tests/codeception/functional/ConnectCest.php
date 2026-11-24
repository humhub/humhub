<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\oauth\tests\codeception\functional;

use FunctionalTester;
use humhub\modules\notification\models\Notification;
use humhub\modules\oauth\components\DeviceConnection;
use humhub\modules\oauth\components\DeviceConnectionRegistry;
use humhub\modules\oauth\events\DeviceConnectionsEvent;
use humhub\modules\oauth\models\Grant;
use humhub\modules\oauth\Module;
use humhub\modules\oauth\notifications\GrantCreatedNotification;
use humhub\modules\oauth\services\PersonalAccessTokenService;
use humhub\modules\user\models\User;
use PHPUnit\Framework\Assert;
use Yii;
use yii\base\Event;

/**
 * The dialog of a device connection: what the device asks for, and a token as its password,
 * without the group permission to create tokens.
 */
class ConnectCest
{
    private const CREATE = '/index-test.php?r=oauth%2Fconnect%2Fcreate&id=';

    public function _before(FunctionalTester $I)
    {
        $this->cleanUp();
        Event::on(DeviceConnectionRegistry::class, DeviceConnectionRegistry::EVENT_COLLECT_CONNECTIONS, [self::class, '_collect']);
    }

    public function _after(FunctionalTester $I)
    {
        Event::off(DeviceConnectionRegistry::class, DeviceConnectionRegistry::EVENT_COLLECT_CONNECTIONS, [self::class, '_collect']);
        $this->cleanUp();
    }

    private function cleanUp(): void
    {
        Grant::deleteAll();
        Notification::deleteAll(['class' => GrantCreatedNotification::class]);
        Module::instance()->settings->delete('allowPersonalAccessTokens');
        Module::instance()->maxPersonalAccessTokensPerUser = null;
        Yii::$app->cache->flush();
    }

    public static function _collect(DeviceConnectionsEvent $event): void
    {
        $event->connections[] = new DeviceConnection(
            'test-drive',
            'Connect a test drive',
            'Your files as a drive.',
            'Test drive',
            ['content:read', 'content:write'],
            ['Address' => 'https://example.org/dav/files/', 'Username' => $event->user->username],
        );
    }

    public function testTheDialogCreatesATokenWithTheScopesOfTheConnection(FunctionalTester $I)
    {
        $I->amUser1();
        $I->amOnRoute('/oauth/connect/index', ['id' => 'test-drive']);
        $I->see('Connect a test drive');
        $I->see('Your files as a drive.');
        $I->seeInField('input[readonly]', 'https://example.org/dav/files/');
        $I->seeInField('DeviceTokenForm[name]', 'Test drive');

        $I->sendAjaxPostRequest(self::CREATE . 'test-drive', ['DeviceTokenForm[name]' => 'Laptop']);
        Assert::assertSame(1, preg_match('/hhpat_[A-Za-z0-9]{38}/', $I->grabPageSource(), $match));
        $I->see('Copy the password now.');
        Assert::assertSame('no-store', $I->grabHttpHeader('Cache-Control'));

        $grant = (new PersonalAccessTokenService())->find($match[0]);
        Assert::assertNotNull($grant);
        Assert::assertSame(2, (int)$grant->user_id);
        Assert::assertSame('test-drive', $grant->issuer);
        Assert::assertSame('Laptop', $grant->name);
        Assert::assertSame(['content:read', 'content:write'], $grant->getScopeIds());

        // An ordinary token of the list
        $I->amOnRoute('/user/security/sessions');
        $I->see('Laptop');
        $I->dontSee($match[0]);
    }

    public function testThereIsNoDialogForAnUnknownConnectionOrWithoutTokens(FunctionalTester $I)
    {
        $I->amUser1();
        $I->amOnRoute('/oauth/connect/index', ['id' => 'other']);
        $I->seeResponseCodeIs(404);

        Module::instance()->settings->set('allowPersonalAccessTokens', false);
        $I->amOnRoute('/oauth/connect/index', ['id' => 'test-drive']);
        $I->seeResponseCodeIs(404);
        $I->sendAjaxPostRequest(self::CREATE . 'test-drive', ['DeviceTokenForm[name]' => 'Laptop']);
        $I->seeResponseCodeIs(404);
        Assert::assertFalse(Grant::find()->exists());
    }

    public function testTheLimitOfTokensIsKept(FunctionalTester $I)
    {
        Module::instance()->maxPersonalAccessTokensPerUser = 1;
        (new PersonalAccessTokenService())->issue(User::findOne(['id' => 2]), 'test-drive', 'Phone', ['content:read']);

        $I->amUser1();
        $I->amOnRoute('/oauth/connect/index', ['id' => 'test-drive']);
        $I->see('You have reached the maximum of 1 personal access tokens.');
        $I->dontSeeElement('input[name="DeviceTokenForm[name]"]');

        $I->sendAjaxPostRequest(self::CREATE . 'test-drive', ['DeviceTokenForm[name]' => 'Laptop']);
        Assert::assertSame(1, (int)Grant::find()->count());
    }

    public function testNoTokenIsCreatedWhileImpersonating(FunctionalTester $I)
    {
        $I->amAdmin();
        Yii::$app->user->getImpersonation()->start(User::findOne(['id' => 2]));

        $I->sendAjaxPostRequest(self::CREATE . 'test-drive', ['DeviceTokenForm[name]' => 'Backdoor']);
        $I->seeResponseCodeIs(403);
        Assert::assertFalse(Grant::find()->exists());
    }
}
