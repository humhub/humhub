<?php

namespace tests\codeception\unit\models;

use humhub\modules\user\models\Profile;
use humhub\modules\user\models\ProfileField;
use humhub\modules\user\models\ProfileFieldCategory;
use humhub\modules\user\models\User;
use humhub\modules\user\models\UserFilter;
use tests\codeception\_support\HumHubDbTestCase;
use Yii;

class UserFilterTest extends HumHubDbTestCase
{
    public function testReturnInstanceForUser()
    {
        $user = UserFilter::findOne(['username' => 'Admin']);
        $user2 = UserFilter::findOne(['username' => 'User1']);
        Yii::$app->user->setIdentity($user);
        $this->assertEquals($user, UserFilter::forUser());
        $this->assertEquals($user2, UserFilter::forUser($user2));
    }

    public function testFilterByPermission()
    {
        $users = User::find()->all();

        $this->assertTrue(is_array(UserFilter::filterByPermission($users, null)));
        $this->assertEquals(count($users), count(UserFilter::filterByPermission($users, null)));
    }

    public function testReturnFilteredUsers()
    {
        $users = UserFilter::filter(User::find(), 'Admin', 5);

        $this->assertEquals(1, count($users));
        $this->assertEquals('Admin', $users[0]->username);
    }

}
