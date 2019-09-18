<?php

namespace tests\codeception\unit\models;

use humhub\modules\space\models\Space;
use humhub\modules\user\models\Group;
use humhub\modules\user\models\Invite;
use humhub\modules\user\models\User;
use tests\codeception\_support\HumHubDbTestCase;
use Yii;
use yii\db\ActiveQuery;

class UserModelTest extends HumHubDbTestCase
{
    public function testReturnArrayOfRules()
    {
        $model = new User();
        $model->scenario = 'registration_email';
        $this->assertTrue(is_array($model->rules()));
    }

    public function testReturnArrayOfAttributeLabels()
    {
        $model = new User();
        $this->assertTrue(is_array($model->attributeLabels()));
    }

    public function testReturnArrayOfScenarios()
    {
        $model = new User();
        $this->assertTrue(is_array($model->scenarios()));
        $this->assertTrue(key_exists('login', $model->scenarios()));
        $this->assertTrue(key_exists('editAdmin', $model->scenarios()));
        $this->assertTrue(key_exists('registration_email', $model->scenarios()));
        $this->assertTrue(key_exists('registration', $model->scenarios()));
    }

    public function testFindIdentityByAccessToken()
    {
        $user = User::findOne(['username' => 'Admin']);
        $this->assertEquals($user, User::findIdentityByAccessToken($user->guid));
    }

    public function testCheckForSystemAdmin()
    {
        $user = User::findOne(['username' => 'Admin']);
        $this->assertTrue($user->super_admin);

        $user = User::findOne(['username' => 'User1']);
        $this->assertFalse($user->super_admin);
    }

    public function testGetProfileInfo()
    {
        $user = new User();
        $user->scenario = 'registration';
        $user->load([
            'username' => 'uniquename'
        ], '');
        $this->assertTrue($user->validate());
        $this->assertTrue($user->save());

        $this->assertNotNull($user->profile);
    }

    public function testReturnAuthKey()
    {
        $user = User::findOne(['username' => 'Admin']);
        $this->assertEquals($user->guid, $user->getAuthKey());
    }

    public function testValidateAuthKey()
    {
        $user = User::findOne(['username' => 'Admin']);
        $this->assertTrue($user->validateAuthKey($user->guid));
    }

    public function testReturnPasswordRelationship()
    {
        $model = new User();
        $this->assertTrue($model->getCurrentPassword() instanceof ActiveQuery);
    }

    public function testHasGroup()
    {
        $user = User::findOne(['username' => 'Admin']);
        $this->assertTrue($user->hasGroup());
    }

    public function testReturnDisplayName()
    {
        $user = User::findOne(['username' => 'Admin']);
        $this->assertEquals('Admin Tester', $user->getDisplayName());

        Yii::$app->settings->set('displayNameFormat', '');
        $this->assertEquals('Admin', $user->getDisplayName());

        $userModule = Yii::$app->getModule('user');
        $userModule->displayNameCallback = function () {
            return 'Callback display name';
        };
        $this->assertEquals('Callback display name', $user->getDisplayName());
    }

    public function testSetUpApproved()
    {
        Yii::$app->getModule('user')->settings->set('auth.defaultUserProfileVisibility', 2);
        $admin = User::findOne(['username' => 'Admin']);
        $model = new Invite();
        $uniqueEmail = Yii::$app->security->generateRandomString(5) . '@email.com';
        $space1 = Space::findOne(['name' => 'Space 1']);
        $publicSpace1 = Space::findOne(['name' => 'Space 3']);
        $publicSpace2 = Space::findOne(['name' => 'Space 4']);

        $model->load([
            'email' => $uniqueEmail,
            'source' => Invite::SOURCE_INVITE,
            'user_originator_id' => $admin->id,
            'space_invite_id' => $space1->id
        ], '');
        $this->assertTrue($model->save());

        $user = new User();
        $user->scenario = 'registration_email';
        $user->load([
            'username' => 'uniquename',
            'email' => $uniqueEmail
        ], '');
        $this->assertTrue($user->validate());
        $this->assertTrue($user->save());

        $this->assertTrue($space1->isMember($user->id));
        $this->assertTrue($publicSpace1->isMember($user->id));
        $this->assertTrue($publicSpace2->isMember($user->id));
    }

    public function testIsCurrentUser()
    {
        $user = User::findOne(['username' => 'Admin']);
        $this->assertFalse($user->isCurrentUser());
        $this->assertFalse($user->is(null));

        Yii::$app->user->setIdentity($user);
        $this->assertTrue($user->isCurrentUser());
    }
}
