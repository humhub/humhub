<?php

namespace tests\codeception\unit\modules\like;

use humhub\modules\comment\models\Comment;
use humhub\modules\like\activities\LikeActivity;
use humhub\modules\like\notifications\NewLikeNotification;
use humhub\modules\like\services\LikeService;
use humhub\modules\notification\components\NotificationManager;
use humhub\modules\notification\models\Notification;
use humhub\modules\post\models\Post;
use humhub\modules\space\models\Space;
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

        $this->assertEquals($likeService->getCount(), 0);
        $this->assertTrue($likeService->like());
        $this->assertEquals($likeService->getCount(), 1);

        $this->assertMailSent(1);
        $this->assertHasNotification(NewLikeNotification::class, Post::findOne(['id' => 1]), 3, 1);
        $this->assertHasActivity(LikeActivity::class, Like::findOne(['content_id' => 1, 'created_by' => 3]));
    }

    public function testNotificationHtmlForPostWithoutText()
    {
        $this->becomeUser('User2');

        $post = new Post(['scenario' => Post::SCENARIO_HAS_FILES, 'message' => '']);
        $post->silentContentCreation = true;
        $post->content->setContainer(Space::findOne(['id' => 2]));
        $post->save();

        $likeService = new LikeService($post);
        $this->assertTrue($likeService->like());

        // the author likes their own post, so no notification was dispatched
        $record = new Notification([
            'class' => NewLikeNotification::class,
            'user_id' => 1,
            'originator_id' => 3,
            'content_id' => $post->content->id,
            'contentcontainer_id' => $post->content->contentcontainer_id,
        ]);
        $this->assertTrue($record->save());
        $html = NotificationManager::fromRecord($record)->asWeb();

        $this->assertStringEndsWith('likes post.', $html);
    }

    public function testLikesOnOnePostGroup()
    {
        // post 2: public post of Admin on their profile
        $post = Post::findOne(['id' => 2]);
        $admin = User::findOne(['id' => 1]);

        $this->becomeUser('User1');
        $this->assertTrue((new LikeService($post))->like());
        $this->becomeUser('User2');
        $this->assertTrue((new LikeService($post))->like());

        $this->assertEqualsNotificationCount(2, NewLikeNotification::class, $post, null, 1);

        $rows = Notification::find()->forUser($admin)->grouped()->andWhere(['notification.class' => NewLikeNotification::class])->all();
        $this->assertCount(1, $rows);
        $notification = NotificationManager::load($rows[0]);
        $this->assertSame(2, $notification->groupCount);
        $web = $notification->asWeb();
        $this->assertStringContainsString(User::findOne(['id' => 2])->displayName, $web);
        $this->assertStringContainsString(User::findOne(['id' => 3])->displayName, $web);
        $this->assertStringContainsString(' likes your ', $notification->getMailSubject());

        // un-like by User2 removes their notification
        $this->assertTrue((new LikeService($post))->unlike());
        $this->assertHasNoNotification(NewLikeNotification::class, $post, 3, 1);
        $this->assertEqualsNotificationCount(1, NewLikeNotification::class, $post, 2, 1);
    }

    public function testLikeOnACommentIsAboutTheComment()
    {
        $post = Post::findOne(['id' => 2]);

        $this->becomeUser('User1');
        $comment = new Comment(['message' => 'Liked comment', 'content_id' => $post->content->id]);
        $this->assertTrue($comment->save());

        $this->becomeUser('User2');
        $this->assertTrue((new LikeService($comment))->like());

        // the comment's author is notified, not the post's
        $this->assertHasNotification(NewLikeNotification::class, $comment, 3, 2);
        $this->assertHasNoNotification(NewLikeNotification::class, $post, 3);

        $record = Notification::findOne(['class' => NewLikeNotification::class, 'user_id' => 2]);
        $this->assertStringContainsString('Liked comment', NotificationManager::load($record)->asWeb());
    }

    public function testDeleteLikesOnContentHardDelete()
    {
        $this->becomeUser('User2');

        $post = Post::findOne(['id' => 1]);
        $likeService = new LikeService($post);

        $this->assertTrue($likeService->like());
        $this->assertNotNull(Like::findOne(['content_id' => $post->content->id]));

        $this->assertTrue($post->hardDelete());
        $this->assertNull(Like::findOne(['content_id' => $post->content->id]));
    }

}
