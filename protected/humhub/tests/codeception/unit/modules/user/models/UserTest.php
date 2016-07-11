<?php

namespace tests\codeception\unit\modules\user\models;

use Yii;
use yii\codeception\DbTestCase;
use Codeception\Specify;
use tests\codeception\fixtures\UserFixture;
use tests\codeception\fixtures\GroupFixture;
use tests\codeception\fixtures\SpaceFixture;
use tests\codeception\fixtures\SpaceMembershipFixture;
use tests\codeception\fixtures\InviteFixture;
use humhub\modules\user\models\User;
use humhub\modules\space\models\Space;
use humhub\modules\user\models\Invite;
use humhub\modules\content\models\Wall;
use humhub\modules\user\models\Group;

class UserTest extends DbTestCase
{

    use Specify;

    /**
     * @inheritdoc
     */
    public function fixtures()
    {
        return [
            'user' => [
                'class' => UserFixture::className(),
            ],
            'invite' => [
                'class' => InviteFixture::className(),
            ],
            'group' => [
                'class' => GroupFixture::className(),
            ],
            'space' => [
                'class' => SpaceFixture::className(),
            ],
            'space_membership' => [
                'class' => SpaceMembershipFixture::className(),
            ],
        ];
    }

    protected function setUp()
    {
        parent::setUp();
        Yii::$app->cache->flush();
    }

    /**
     * Tests if user automatically added to the group´s default space
     */
    public function testCreateGroupSpaceAdd()
    {
        Yii::$app->getModule('user')->settings->set('auth.needApproval', 0);

        $space = Space::findOne(['id' => 1]);

        $user = new User();
        $user->username = "TestGroup";
        $user->email = "group@example.com";
        $this->assertTrue($user->save());

        $group = Group::findOne(['id' => 1]);
        $group->addUser($user);

        $this->assertTrue($space->isMember($user->id));
    }

    public function testInviteToSpace()
    {
        Yii::$app->getModule('user')->settings->set('auth.needApproval', 0);

        $invite = new Invite();
        $invite->user_originator_id = 1;
        $invite->space_invite_id = 2;
        $invite->email = "testspaceinvite@example.com";
        $invite->source = Invite::SOURCE_INVITE;
        $this->assertTrue($invite->save());

        $space = Space::findOne(['id' => 2]);
        $user = new User();
        $user->username = "TestSpaceInvite";
        $user->email = "testspaceinvite@example.com";
        $this->assertTrue($user->save());
        $this->assertTrue($space->isMember($user->id));
    }

    /**
     * Tests spaces which automatically adds new members
     * Fixture Space 3
     */
    public function testAutoAddSpace()
    {
        $space2 = Space::findOne(['id' => 2]);
        $space3 = Space::findOne(['id' => 3]);

        $user = new User();
        $user->username = "TestSpaceAutoAdd";
        $user->email = "testautoadd@example.com";

        $this->assertTrue($user->save());

        $this->assertFalse($space2->isMember($user->id)); // not assigned
        $this->assertTrue($space3->isMember($user->id)); // via global assign
    }


    public function testAutoWallCreation()
    {
        $user = new User();
        $user->username = "wallTest";
        $user->email = "wall@example.com";
        $this->assertTrue($user->save());

        $this->assertNotNull($user->wall_id);
        $wall = Wall::findOne(['id' => $user->wall_id]);
        $this->assertNotNull($wall);
    }

}
