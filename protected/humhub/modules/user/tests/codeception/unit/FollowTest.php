<?php

namespace tests\codeception\unit;

use Yii;
use tests\codeception\_support\HumHubDbTestCase;
use Codeception\Specify;
use humhub\modules\user\models\User;
use humhub\modules\user\models\Follow;

class FollowTest extends HumHubDbTestCase
{

    use Specify;

    public function testFollowUser()
    {
        $this->becomeUser('User1');

        $user = User::findOne(['id' => 1]);
        $this->assertTrue($user->follow());

        $follow = Follow::findOne(['object_model' => User::class, 'object_id' => 1, 'user_id' => 2]);

        $this->assertNotNull($follow);
        $this->assertMailSent(1, 'User follow notification Mail.');
        $this->assertHasNotification(\humhub\modules\user\notifications\Followed::class, $follow, Yii::$app->user->id, 'Approval Request Notification');
    }

}
