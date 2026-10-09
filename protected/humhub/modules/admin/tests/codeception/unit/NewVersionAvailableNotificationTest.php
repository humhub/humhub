<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace tests\codeception\unit\modules\admin;

use humhub\modules\admin\notifications\NewVersionAvailableNotification;
use humhub\modules\notification\components\NotificationCategory;
use humhub\modules\notification\components\NotificationManager;
use humhub\modules\notification\components\NotificationPriority;
use humhub\modules\notification\models\Notification;
use humhub\modules\user\models\Group;
use humhub\modules\user\models\User;
use tests\codeception\_support\HumHubDbTestCase;
use yii\helpers\Url;

class NewVersionAvailableNotificationTest extends HumHubDbTestCase
{
    public function testDispatchToTheAdministrators()
    {
        NewVersionAvailableNotification::send(
            Group::getAdminGroup()->getUsers(),
            payload: ['version' => '9.9.9'],
        );

        $record = Notification::findOne(['class' => NewVersionAvailableNotification::class, 'user_id' => 1]);
        $this->assertNotNull($record);
        $this->assertNull($record->originator_id);
        $this->assertNull($record->source_record_id);
        $this->assertSame(NotificationPriority::Normal->value, (int)$record->priority);
        $this->assertSame(NotificationCategory::ID_ADMIN, NewVersionAvailableNotification::category()->id);

        $notification = NotificationManager::load($record);
        $this->assertSame('There is a new HumHub Version (9.9.9) available.', $notification->asWeb());
        $this->assertSame('There is a new HumHub Version (9.9.9) available.', $notification->asMailText());
        $this->assertSame('There is a new HumHub Version (9.9.9) available.', $notification->asMailSubject());
        $this->assertSame(Url::to(['/admin/information/about']), $notification->getUrl());
        $this->assertSame(Url::to(['/admin/information/about'], true), $notification->getUrl(true));

        // the admin group of the fixtures has no other members than the admin
        $count = (int)Notification::find()->where(['class' => NewVersionAvailableNotification::class])->count();
        $this->assertSame((int)Group::getAdminGroup()->getUsers()->count(), $count);
    }

    public function testDeleteWithoutUserRemovesAllRows()
    {
        NewVersionAvailableNotification::send(
            [User::findOne(['id' => 1]), User::findOne(['id' => 2])],
            payload: ['version' => '9.9.9'],
        );
        $this->assertSame(2, (int)Notification::find()->where(['class' => NewVersionAvailableNotification::class])->count());

        $this->assertSame(2, NewVersionAvailableNotification::revoke());
        $this->assertSame(0, (int)Notification::find()->where(['class' => NewVersionAvailableNotification::class])->count());
    }
}
