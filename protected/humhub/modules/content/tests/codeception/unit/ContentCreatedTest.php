<?php

namespace tests\codeception\unit;

use humhub\modules\content\models\Content;
use humhub\modules\post\models\Post;
use Yii;
use tests\codeception\_support\HumHubDbTestCase;
use Codeception\Specify;
use humhub\modules\space\models\Space;
use humhub\modules\user\models\User;

class ContentCreatedTest extends HumHubDbTestCase
{

    use Specify;

    /**
     * Test CreateContent notification for a space follower with send_notification setting (see user_follow fixture)
     */
    public function testFollowContentNotification()
    {
        $this->becomeUser('User2');

        $post = new Post(['message' => 'MyTestContent']);
        $post->content->setContainer(Space::findOne(['id' => 2]));
        $post->content->visibility = Content::VISIBILITY_PUBLIC;
        $post->save();

        // Note Admin is following Space2 so we expect one notification mail.
        $this->assertMailSent(1, 'ContentCreated Notification Mail sent');
    }

    /**
     * Disable mail notifications for a follower.
     */
    public function testFollowerDisableMailNotification()
    {
        // Admin is following space
        $admin = User::findOne(['id' => 1]);

        // Disable $user1 notification settings.
        Yii::$app->getModule('notification')->settings->user($admin)->set('notification.content_created_email', 0);

        $this->becomeUser('User2');

        $post = new Post(['message' => 'MyTestContent']);
        $post->content->setContainer(Space::findOne(['id' => 2]));
        $post->content->visibility = Content::VISIBILITY_PUBLIC;
        $post->save();

        $this->assertMailSent(0, 'ContentCreated Notification Mail sent');
    }

    /**
     * Test the notifyUsersOfNewContent field when creating new content.
     */
    public function testNotifyUsersOfNewContent()
    {
        $this->becomeUser('User2');

        $post = new Post(['message' => 'MyTestContent']);
        $post->content->setContainer(Space::findOne(['id' => 2]));
        $post->content->visibility = Content::VISIBILITY_PUBLIC;
        // Add User1
        $post->content->notifyUsersOfNewContent = [User::findOne(['id' => 2])];
        $post->save();

        // We expect two notification mails one for following User1 and one for notifyUserOfNewContent User3.
        $this->assertMailSent(2, 'ContentCreated Notification Mail sent');
    }

    /**
     * Check that space follower are not notified about private content.
     */
    public function testExcludeFollowerForPrivateCotnentNotification()
    {
        $this->becomeUser('User2');

        $post = new Post(['message' => 'MyTestContent']);
        $post->content->setContainer(Space::findOne(['id' => 2]));
        // Add User1
        $post->content->notifyUsersOfNewContent = [User::findOne(['id' => 2])];
        $post->content->visibility = Content::VISIBILITY_PRIVATE;
        $post->save();

        // We expect two notification mails one for following User1 and one for notifyUserOfNewContent User3.
        $this->assertMailSent(1, 'ContentCreated Notification Mail sent');
    }

    /**
     * Make sure we do not send duplicate notification if we set an space follower again as notifyUserofNewContent.
     */
    public function testNotifyDuplicatedUser()
    {
        $this->becomeUser('User2');

        $post = new Post(['message' => 'MyTestContent']);
        $post->content->setContainer(Space::findOne(['id' => 2]));
        $post->content->visibility = Content::VISIBILITY_PUBLIC;
        // Add an already following user again in the notifyUser field.
        $post->content->notifyUsersOfNewContent = [User::findOne(['id' => 1])];
        $post->save();

        // We only one notification
        $this->assertMailSent(1, 'ContentCreated Notification Mail sent');
    }

    /**
     * Check the sending of notifications for space_members with active send_notifications setting.
     */
    public function testSpaceMemberNotification()
    {
        $this->becomeUser('User3');

        $post = new Post(['message' => 'MyTestContent']);
        $post->content->setContainer(Space::findOne(['id' => 3]));
        $post->content->visibility = Content::VISIBILITY_PUBLIC;
        $post->save();

        $this->assertMailSent(1, 'ContentCreated Notification Mail sent');
    }

    /**
     * Make sure the originator of a new content does not receive a notification himself.
     */
    public function testDontSendNotificationToOriginator()
    {
        $this->becomeUser('User1');

        $post = new Post(['message' => 'MyTestContent']);
        $post->content->setContainer(Space::findOne(['id' => 3]));
        $post->content->visibility = Content::VISIBILITY_PUBLIC;
        $post->save();

        $this->assertMailSent(0, 'ContentCreated Notification Mail sent');
    }

    /**
     * Test the deactivation of the mail target for new content.
     */
    public function testDeactivateMailNotificationAsSpaceMember()
    {
        $this->becomeUser('User3');

        // Disable $user1 notification settings.
        Yii::$app->getModule('notification')->settings->user(User::findOne(['id' => 2]))->set('notification.content_created_email', 0);

        $post = new Post(['message' => 'MyTestContent']);
        $post->content->setContainer(Space::findOne(['id' => 3]));
        $post->content->visibility = Content::VISIBILITY_PUBLIC;
        $post->save();

        $this->assertMailSent(0, 'ContentCreated Notification Mail sent');
    }
    
    /**
     * Admin and User2 are member of Space1 -> Space1 is set to default notification space.
     * After User2 posts new content Admin user should automatically be notified.
     */
    public function testDefaultSpaceFollowPrivatePostNotification()
    {
        $this->becomeUser('User2');

        Yii::$app->notification->setSpaces(['5396d499-20d6-4233-800b-c6c86e5fa34a']);
        $post = new Post(['message' => 'MyTestContent']);
        $post->content->setContainer(Space::findOne(['id' => 1]));
        $post->content->visibility = Content::VISIBILITY_PRIVATE;
        $post->save();

        // Note Admin is following Space2 so we expect one notification mail.
        $this->assertMailSent(1, 'ContentCreated Notification Mail sent');
    }
    
    /**
     * Admin and User2 are member of Space1 -> Space1 is set to default notification space.
     * Admin explicitly removes the default notification space.
     */
    public function testExplicitlyDisableDefaultNotificationSpace()
    {
        $this->becomeUser('Admin');

        Yii::$app->notification->setSpaces(['5396d499-20d6-4233-800b-c6c86e5fa34a']);
        
        // Setting empty spaceguids settings
        $settings = new \humhub\modules\notification\models\forms\NotificationSettings(['user' => User::findOne(['id' => 1])]);
        $settings->save();
        
        $this->becomeUser('User2');
        
        $post = new Post(['message' => 'MyTestContent']);
        $post->content->setContainer(Space::findOne(['id' => 1]));
        $post->content->visibility = Content::VISIBILITY_PRIVATE;
        $post->save();

        // Note Admin is following Space2 so we expect one notification mail.
        $this->assertMailSent(0, 'ContentCreated Notification Mail sent');
    }
    
    /**
     * After User2 posts new public post Admin all other users should be notified.
     */
    public function testDefaultSpaceFollowPublicPostNotification()
    {
        $this->becomeUser('User2');

        Yii::$app->notification->setSpaces(['5396d499-20d6-4233-800b-c6c86e5fa34a']);
        $post = new Post(['message' => 'MyTestContent']);
        $post->content->setContainer(Space::findOne(['id' => 1]));
        $post->content->visibility = Content::VISIBILITY_PUBLIC;
        $post->save();

        // Note Admin is following Space2 so we expect one notification mail.
        $this->assertMailSent(3, 'ContentCreated Notification Mail sent');
    }
    
    /**
     * Disable space as member and post public content.
     */
    public function testExplicitlyDisableDefaultNotificationPublicMember()
    {
        $this->becomeUser('Admin');

        Yii::$app->notification->setSpaces(['5396d499-20d6-4233-800b-c6c86e5fa34a']);
        
        // Setting empty spaceguids settings
        $settings = new \humhub\modules\notification\models\forms\NotificationSettings(['user' => User::findOne(['id' => 1])]);
        $settings->save();
        
        $this->becomeUser('User2');
        
        $post = new Post(['message' => 'MyTestContent']);
        $post->content->setContainer(Space::findOne(['id' => 1]));
        $post->content->visibility = Content::VISIBILITY_PUBLIC;
        $post->save();

        // Note Admin is following Space2 so we expect one notification mail.
        $this->assertMailSent(2, 'ContentCreated Notification Mail sent');
    }
    
    /**
     * Disable space as member and post public content.
     */
    public function testExplicitlyDisableDefaultNotificationPublic()
    {
        $this->becomeUser('User1');

        Yii::$app->notification->setSpaces(['5396d499-20d6-4233-800b-c6c86e5fa34a']);
        
        // Setting empty spaceguids settings
        $settings = new \humhub\modules\notification\models\forms\NotificationSettings(['user' => User::findOne(['id' => 2])]);
        $settings->save();
        
        $this->becomeUser('User2');
        
        $post = new Post(['message' => 'MyTestContent']);
        $post->content->setContainer(Space::findOne(['id' => 1]));
        $post->content->visibility = Content::VISIBILITY_PUBLIC;
        $post->save();

        // Note Admin is following Space2 so we expect one notification mail.
        $this->assertMailSent(2, 'ContentCreated Notification Mail sent');
    }
    
    /**
     * Disable space as member and post public content.
     */
    public function testExplicitlyDisableDefaultNotificationPublic2()
    {
        $this->becomeUser('User1');

        Yii::$app->notification->setSpaces(['5396d499-20d6-4233-800b-c6c86e5fa34a']);
        
        // Setting empty spaceguids settings
        $settings = new \humhub\modules\notification\models\forms\NotificationSettings(['user' => User::findOne(['id' => 2])]);
        $settings->save();
        
        $this->becomeUser('Admin');

        Yii::$app->notification->setSpaces(['5396d499-20d6-4233-800b-c6c86e5fa34a']);
        
        // Setting empty spaceguids settings
        $settings = new \humhub\modules\notification\models\forms\NotificationSettings(['user' => User::findOne(['id' => 1])]);
        $settings->save();
        
        $this->becomeUser('User2');
        
        $post = new Post(['message' => 'MyTestContent']);
        $post->content->setContainer(Space::findOne(['id' => 1]));
        $post->content->visibility = Content::VISIBILITY_PUBLIC;
        $post->save();

        // Note Admin is following Space2 so we expect one notification mail.
        $this->assertMailSent(1, 'ContentCreated Notification Mail sent');
    }
}
