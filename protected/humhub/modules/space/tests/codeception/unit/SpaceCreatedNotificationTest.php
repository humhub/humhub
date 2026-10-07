<?php

namespace tests\codeception\unit\modules\space;

use humhub\modules\notification\components\NotificationManager;
use humhub\modules\notification\models\Notification;
use humhub\modules\space\models\Space;
use humhub\modules\space\notifications\SpaceCreatedNotification;
use tests\codeception\_support\HumHubDbTestCase;
use Yii;

class SpaceCreatedNotificationTest extends HumHubDbTestCase
{
    public function testSpaceCreatedByAUserWithoutManageSpacesNotifiesTheSpaceManagers()
    {
        $this->becomeUser('User1');

        // without validation: User1 may not create spaces in the test fixture
        $space = new Space(['name' => 'New Space', 'visibility' => Space::VISIBILITY_REGISTERED_ONLY]);
        $this->assertTrue($space->save(false));

        // the system admin (user 1) may manage spaces
        $this->assertHasNotification(SpaceCreatedNotification::class, $space, Yii::$app->user->id, 1);

        $notification = NotificationManager::load(Notification::findOne(['class' => SpaceCreatedNotification::class, 'user_id' => 1]));
        $this->assertSame($space->getUrl(), $notification->getUrl());
        $this->assertSame(Yii::$app->user->identity->displayName . ' created the new Space New Space', $notification->getMailSubject());
    }

    public function testPrivateSpaceCreatedByAUserWithoutManageSpacesNotifiesTheSpaceManagers()
    {
        $this->becomeUser('User1');

        // without validation: User1 may not create spaces in the test fixture
        $space = new Space(['name' => 'New Private Space', 'visibility' => Space::VISIBILITY_NONE]);
        $this->assertTrue($space->save(false));

        // the admin is no member of the private space
        $this->assertHasNotification(SpaceCreatedNotification::class, $space, Yii::$app->user->id, 1);
    }

    public function testSpaceCreatedByASpaceManagerNotifiesNobody()
    {
        $this->becomeUser('Admin');

        $space = new Space(['name' => 'New Space', 'visibility' => Space::VISIBILITY_REGISTERED_ONLY]);
        $this->assertTrue($space->save(false));

        $this->assertHasNoNotification(SpaceCreatedNotification::class, $space);
    }
}
