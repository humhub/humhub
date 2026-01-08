<?php

namespace tests\codeception\unit\modules\like;

use humhub\modules\like\activities\Liked;
use humhub\modules\like\notifications\NewLike;
use humhub\modules\like\services\LikeService;
use humhub\modules\post\models\Post;
use Yii;
use humhub\modules\user\models\User;
use tests\codeception\_support\HumHubDbTestCase;
use Codeception\Specify;
use humhub\modules\like\models\Like;

class LikeTest extends HumHubDbTestCase
{
    use Specify;

    public function testLikePost()
    {
        $this->becomeUser('User2');

        $likeService = new LikeService(Post::findOne(['id' => 1]));
        Yii::$app->getModule('notification')->settings->user(User::findOne(['id' => 1]))->set('notification.like_email', 1);

        $this->assertEquals($likeService->getCount(), 0);
        $this->assertTrue($likeService->like());
        $this->assertEquals($likeService->getCount(), 1);

        $this->assertMailSent(1);
        $this->assertHasNotification(NewLike::class, Like::findOne(['content_id' => 1, 'created_by' => 3]));
        $this->assertHasActivity(Liked::class, Like::findOne(['content_id' => 1, 'created_by' => 3]));
    }

}
