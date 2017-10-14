<?php

namespace tests\codeception\unit\modules\friendship;

use Yii;
use tests\codeception\_support\HumHubDbTestCase;
use Codeception\Specify;
use humhub\modules\friendship\models\Friendship;
use humhub\modules\user\models\User;

class FriendshipTest extends HumHubDbTestCase
{

    use Specify;

    /**
     * Create a Mock Content class and assign a notify user save it and check if an email was sent and test wallout.
     */
    public function testAcceptFriendShip()
    {
        Yii::$app->getModule('friendship')->settings->set('enable', 1);

        $this->becomeUser('User2');
        $friendUser = User::findOne(['id' => 2]);
        
        $this->assertEquals(Friendship::getStateForUser($friendUser, Yii::$app->user), Friendship::STATE_NONE, 'Check Status before sent');
        
        // Request Friendship
        $this->assertTrue(Friendship::add(Yii::$app->user->getIdentity(), $friendUser));
        $this->assertMailSent(1, 'Friendship request mail sent.');
        
        $fiendship = Friendship::findOne(['user_id' => Yii::$app->user->id, 'friend_user_id' => 2]);
        $this->assertNotNull($fiendship, 'Friendship model persisted.');
        $this->assertEquals(Friendship::getStateForUser(Yii::$app->user, $friendUser), Friendship::STATE_REQUEST_SENT, 'Check Sent Status');
        $this->assertEquals(Friendship::getStateForUser($friendUser, Yii::$app->user), Friendship::STATE_REQUEST_RECEIVED, 'Check Received Status');
        
        // Accept friendship
        $this->assertTrue(Friendship::add($friendUser, Yii::$app->user->getIdentity()));
        $this->assertEquals(Friendship::getStateForUser($friendUser, Yii::$app->user), Friendship::STATE_FRIENDS, 'Check Friend Status');
        $this->assertMailSent(2, 'Friendship acknowledged mail sent.');
    }
    
    public function testDeclineFriendShip()
    {
        Yii::$app->getModule('friendship')->settings->set('enable', 1);
        
        $this->becomeUser('User2');
        $friendUser = User::findOne(['id' => 2]);
        
        $this->assertEquals(Friendship::getStateForUser($friendUser, Yii::$app->user), Friendship::STATE_NONE, 'Check Status before sent');
        
        // Request Friendship
        $this->assertTrue(Friendship::add(Yii::$app->user->getIdentity(), $friendUser));
        $this->assertMailSent(1, 'Friendship request mail sent.');
        
        $fiendship = Friendship::findOne(['user_id' => Yii::$app->user->id, 'friend_user_id' => 2]);
        $this->assertNotNull($fiendship, 'Friendship model persisted.');
        $this->assertEquals(Friendship::getStateForUser(Yii::$app->user, $friendUser), Friendship::STATE_REQUEST_SENT, 'Check Sent Status');
        $this->assertEquals(Friendship::getStateForUser($friendUser, Yii::$app->user), Friendship::STATE_REQUEST_RECEIVED, 'Check Received Status');
        
        // Cancel request
        Friendship::cancel($friendUser, Yii::$app->user->getIdentity());
        $this->assertEquals(Friendship::getStateForUser($friendUser, Yii::$app->user), Friendship::STATE_NONE, 'Check Friend Status');
        $this->assertMailSent(2, 'Friendship acknowledged mail sent.');
    }
}
