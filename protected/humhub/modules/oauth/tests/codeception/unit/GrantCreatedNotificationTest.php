<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\oauth\tests\codeception\unit;

use humhub\modules\notification\components\NotificationManager;
use humhub\modules\notification\models\Notification;
use humhub\modules\oauth\models\Grant;
use humhub\modules\oauth\notifications\GrantCreatedNotification;
use humhub\modules\oauth\Module;
use humhub\modules\oauth\services\PersonalAccessTokenService;
use humhub\modules\user\models\User;
use tests\codeception\_support\HumHubDbTestCase;
use yii\helpers\Url;

/**
 * The queue of the test application is synchronous, so the notification is written inline.
 */
class GrantCreatedNotificationTest extends HumHubDbTestCase
{
    protected function _before(): void
    {
        parent::_before();
        Module::instance()->settings->set('patMaxLifetimeDays', 0);
        Notification::deleteAll(['class' => GrantCreatedNotification::class]);
    }

    public function testCreatingATokenNotifiesTheOwner()
    {
        (new PersonalAccessTokenService())->create(User::findOne(['id' => 2]), 'CI script', ['content:read'], 30);

        $rows = Notification::find()->where(['class' => GrantCreatedNotification::class])->all();
        $this->assertCount(1, $rows);
        $this->assertSame(2, (int)$rows[0]->user_id);

        $notification = NotificationManager::load($rows[0]);
        $this->assertInstanceOf(GrantCreatedNotification::class, $notification);
        $this->assertStringContainsString('CI script', strip_tags($notification->asWeb()));
        $this->assertSame(Url::to(['/user/security/sessions']), $notification->getUrl());
    }

    public function testNotificationSurvivesTheRevokedGrant()
    {
        [, $grant] = (new PersonalAccessTokenService())->create(User::findOne(['id' => 2]), 'x', ['content:read'], 30);
        Grant::deleteAll(['id' => $grant->id]);

        $this->assertSame(1, (int)Notification::find()->where(['class' => GrantCreatedNotification::class])->count());
    }
}
