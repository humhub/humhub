<?php

namespace tests\codeception\unit\modules\like;

use humhub\modules\like\models\Like;
use humhub\modules\like\services\LikeService;
use humhub\modules\post\models\Post;
use humhub\modules\user\models\User;
use humhub\tests\codeception\unit\components\MutexMock;
use tests\codeception\_support\HumHubDbTestCase;
use Yii;

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

    public function testLikeRunsUnderMutex()
    {
        $mutex = new MutexMock();
        Yii::$app->set('mutex', $mutex);

        $this->becomeUser('User1');
        $post = Post::findOne(['id' => 1]);

        $this->assertTrue((new LikeService($post))->like());

        $this->assertCount(1, $mutex->acquiredLocks);
        $this->assertStringContainsString((string)$post->content->id, $mutex->acquiredLocks[0]);
        $this->assertEquals($mutex->acquiredLocks, $mutex->releasedLocks);
    }

    /**
     * A like on the content itself stores NULL in `content_addon_record_id`, and
     * MySQL/MariaDB treat NULLs in a unique index as distinct — so the unique
     * index on `like` cannot catch a concurrent duplicate here. The lock and the
     * re-check inside it have to.
     */
    public function testSkipsLikeWhenConcurrentlyCreated()
    {
        $this->becomeUser('User1');
        $post = Post::findOne(['id' => 1]);
        $userId = Yii::$app->user->id;

        // Simulate a concurrent request that created the like while this one
        // was still waiting for the lock
        $mutex = new MutexMock();
        $mutex->onAcquire = function () use ($post, $userId) {
            Yii::$app->db->createCommand()->insert(Like::tableName(), [
                'content_id' => $post->content->id,
                'content_addon_record_id' => null,
                'created_at' => date('Y-m-d H:i:s'),
                'created_by' => $userId,
            ])->execute();
        };
        Yii::$app->set('mutex', $mutex);

        $this->assertFalse((new LikeService($post))->like());

        $this->assertEquals(1, Like::find()
            ->where(['content_id' => $post->content->id, 'created_by' => $userId])
            ->andWhere('content_addon_record_id IS NULL')
            ->count());
    }

    public function testLikesWhenMutexUnavailable()
    {
        $mutex = new MutexMock(['available' => false]);
        Yii::$app->set('mutex', $mutex);

        $this->becomeUser('User1');
        $post = Post::findOne(['id' => 1]);

        // Even without the lock the like must be created, but a lock that was
        // never acquired must not be released
        $this->assertTrue((new LikeService($post))->like());
        $this->assertCount(1, $mutex->acquiredLocks);
        $this->assertCount(0, $mutex->releasedLocks);
    }
}
