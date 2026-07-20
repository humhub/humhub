<?php

namespace tests\codeception\unit\modules\like;

use humhub\modules\like\services\LikeService;
use humhub\modules\post\models\Post;
use humhub\modules\user\models\User;
use tests\codeception\_support\HumHubDbTestCase;

class LikeServiceTest extends HumHubDbTestCase
{
    public function testConstructWithExplicitUser()
    {
        $this->becomeUser('User1');
        $post = Post::findOne(['id' => 1]);
        $this->assertTrue((new LikeService($post))->like());

        // A service for another, explicitly passed user must not report the
        // session user's like
        $serviceForOther = new LikeService($post, User::findOne(['username' => 'User2']));
        $this->assertFalse($serviceForOther->hasLiked());

        $serviceForLiker = new LikeService($post, User::findOne(['username' => 'User1']));
        $this->assertTrue($serviceForLiker->hasLiked());
    }
}
