<?php

namespace tests\codeception\unit\models;

use humhub\modules\space\models\Space;
use humhub\modules\user\models\Invite;
use humhub\modules\user\models\User;
use tests\codeception\_support\HumHubDbTestCase;
use Yii;
use yii\db\ActiveQuery;

class InviteModelTest extends HumHubDbTestCase
{
    public function testReturnTableName()
    {
        $this->assertEquals('user_invite', Invite::tableName());
    }

    public function testReturnArrayOfRules()
    {
        $model = new Invite();
        $this->assertTrue(is_array($model->rules()));
    }

    public function testReturnScenarios()
    {
        $model = new Invite();
        $scenarios = $model->scenarios();
        $this->assertTrue(is_array($scenarios));
        $this->assertTrue(key_exists('invite', $scenarios));
        $this->assertEquals(['email'], $scenarios['invite']);
    }

    public function testReturnArrayOfAttributeLabels()
    {
        $model = new Invite();
        $this->assertTrue(is_array($model->attributeLabels()));
    }

    public function testSelfInvite()
    {
        $model = new Invite();
        $uniqueEmail = Yii::$app->security->generateRandomString(5) . '@email.com';
        $model->load(['email' => $uniqueEmail], '');
        $this->assertTrue($model->save());

        Yii::$app->getModule('user')->settings->set('auth.anonymousRegistration', false);
        $this->assertFalse($model->selfInvite());

        Yii::$app->getModule('user')->settings->set('auth.anonymousRegistration', true);
        $this->assertTrue($model->selfInvite());

        $this->assertSentEmail(1);

        $this->assertEqualsLastEmailSubject(sprintf('Welcome to %s', Yii::$app->name));
    }

    public function testInviteToSpace()
    {
        $model = new Invite();
        $uniqueEmail = Yii::$app->security->generateRandomString(5) . '@email.com';
        $user = User::findOne(['username' => 'Admin']);
        $model->load([
            'email' => $uniqueEmail,
            'source' => Invite::SOURCE_INVITE,
            'space_invite_id' => Space::findOne(['name' => 'Space 1'])->id,
            'user_originator_id' => $user->id
        ], '');
        $this->assertTrue($model->save());

        Yii::$app->getModule('user')->settings->set('auth.anonymousRegistration', true);
        Yii::$app->user->setIdentity($user);

        $model->sendInviteMail();
        $model->delete();
        $this->assertSentEmail(1);
        $this->assertEqualsLastEmailSubject(sprintf("You've been invited to join %s on %s", $model->space->name, Yii::$app->name));
    }

    public function testInviteOtherUser()
    {
        $model = new Invite();
        $uniqueEmail = Yii::$app->security->generateRandomString(5) . '@email.com';
        $user = User::findOne(['username' => 'Admin']);
        $model->load([
            'email' => $uniqueEmail,
            'source' => Invite::SOURCE_INVITE,
            'user_originator_id' => $user->id
        ], '');
        $this->assertTrue($model->save());

        Yii::$app->getModule('user')->settings->set('auth.anonymousRegistration', true);
        Yii::$app->user->setIdentity($user);
        $model->sendInviteMail();
        $model->delete();

        $this->assertSentEmail(1);

        $this->assertEqualsLastEmailSubject(sprintf("You've been invited to join %s", Yii::$app->name));
    }

    public function testReturnOriginatorRelationship()
    {
        $model = new Invite();
        $this->assertTrue($model->getOriginator() instanceof ActiveQuery);
    }

    public function testReturnSpaceRelationship()
    {
        $model = new Invite();
        $this->assertTrue($model->getSpace() instanceof ActiveQuery);
    }

    public function testCheckIfSelfInviteAllowed()
    {
        $model = new Invite();
        Yii::$app->getModule('user')->settings->set('auth.anonymousRegistration', false);
        $this->assertFalse((boolean) $model->allowSelfInvite());

        Yii::$app->getModule('user')->settings->set('auth.anonymousRegistration', true);
        $this->assertTrue((boolean) $model->allowSelfInvite());
    }
}
