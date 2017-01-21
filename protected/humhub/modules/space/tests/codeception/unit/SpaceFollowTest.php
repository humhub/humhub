<?php

namespace tests\codeception\unit\modules\space;

use Yii;
use tests\codeception\_support\HumHubDbTestCase;
use Codeception\Specify;
use humhub\modules\space\models\Space;
use humhub\modules\user\models\Follow;

class SpaceFollowTest extends HumHubDbTestCase
{

    use Specify;

    public function testSpaceFollow()
    {
        $this->becomeUser('User1');

        // Follow Space 2
        $space = Space::findOne(['id' => 2]);
        $space->follow(null, false);
        
        // Check if follow record was saved
        $follow = Follow::findOne(['object_model' => Space::class, 'object_id' => 2, 'user_id' => 2]);
        $this->assertNotNull($follow);
        $this->assertFalse(boolval($follow->send_notifications));
        
        // Get all spaces this user follows and check if the new space is included
        $spaces = Follow::getFollowedSpacesQuery(Yii::$app->user->getIdentity())->all();
        $this->assertEquals(count($spaces), 1);
        $this->assertEquals($spaces[0]->id, 2);
        
        // Get all followers of Space 2 and check if the user is included
        $followers = Follow::getFollowersQuery($space)->all();
        $this->assertEquals(count($followers), 2);
        
        if($followers[0]->id == 2) {
           $this->assertTrue(true);
        } else if($followers[1]->id == 2) {
            $this->assertTrue(true);
        } else {
            $this->assertTrue(false, 'User not in follower list.');
        }
    }

}
