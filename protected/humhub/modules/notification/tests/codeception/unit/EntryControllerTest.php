<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\notification\tests\codeception\unit;

use humhub\modules\notification\models\Notification;
use humhub\modules\user\models\User;
use humhub\modules\user\notifications\FollowedNotification;
use tests\codeception\_support\HumHubDbTestCase;
use Yii;

/**
 * The link of a notification, `notification/entry`.
 */
class EntryControllerTest extends HumHubDbTestCase
{
    public $fixtureConfig = ['default'];

    /**
     * The followers of a user are one group, but the link of each notification of the group leads to its own
     * target: the profile of the follower, not that of the newest one.
     */
    public function testEntryOfAnOlderMemberOfAGroupLeadsToItsOwnTarget()
    {
        $admin = User::findOne(['id' => 1]);

        $this->becomeUser('User1');
        $this->assertTrue($admin->follow());
        $this->becomeUser('User2');
        $this->assertTrue($admin->follow());

        $records = Notification::find()->where(['class' => FollowedNotification::class, 'user_id' => $admin->id])->orderBy('id')->all();
        $this->assertCount(2, $records);
        $this->assertSame((int)$records[0]->grouping_key, (int)$records[1]->grouping_key, 'the followers are one group');

        $this->becomeUser('Admin');

        foreach ($records as $record) {
            $response = Yii::$app->runAction('notification/entry/index', ['id' => $record->id]);
            $this->assertSame(
                User::findOne(['id' => $record->originator_id])->getUrl(true),
                $response->headers->get('Location'),
            );
        }
    }
}
