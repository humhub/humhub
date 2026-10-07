<?php

namespace tests\codeception\unit;

use humhub\modules\content\models\Content;
use humhub\modules\content\notifications\ContentCreatedNotification;
use humhub\modules\notification\components\NotificationGroup;
use humhub\modules\notification\components\NotificationManager;
use humhub\modules\notification\models\Notification;
use humhub\modules\notification\services\NotificationSettingsService;
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
        $this->assertMailSent(1);
    }

    public function testSilentContentCreation()
    {
        $this->becomeUser('User2');

        $post = new Post(['message' => 'MyTestContent']);
        $post->silentContentCreation = true;
        $post->content->setContainer(Space::findOne(['id' => 2]));
        $post->content->visibility = Content::VISIBILITY_PUBLIC;
        $post->save();

        // Note Admin is following Space2 so we expect one notification mail.
        $this->assertMailSent(0);
    }

    /**
     * Disable mail notifications for a follower.
     */
    public function testFollowerDisableMailNotification()
    {
        // Admin is following space
        $admin = User::findOne(['id' => 1]);

        // Disable $user1 notification settings.
        $this->disableContentMails($admin);

        $this->becomeUser('User2');

        $post = new Post(['message' => 'MyTestContent']);
        $post->content->setContainer(Space::findOne(['id' => 2]));
        $post->content->visibility = Content::VISIBILITY_PUBLIC;
        $post->save();

        $this->assertMailSent(0);
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
        $this->assertMailSent(2);
    }

    /**
     * A follower who is also picked in the content form gets one notification, with the wording
     * of the explicit notification.
     */
    public function testExplicitlyNotifiedUserGetsOneNotificationWithTheExplicitWording()
    {
        $this->becomeUser('User2');

        $post = new Post(['message' => 'MyTestContent']);
        $post->content->setContainer(Space::findOne(['id' => 2]));
        $post->content->visibility = Content::VISIBILITY_PUBLIC;
        // Admin follows Space 2
        $post->content->notifyUsersOfNewContent = [User::findOne(['id' => 1])];
        $this->assertTrue($post->save());

        $this->assertEqualsNotificationCount(1, ContentCreatedNotification::class, $post, null, 1);

        $record = Notification::findOne(['class' => ContentCreatedNotification::class, 'user_id' => 1]);
        $notification = NotificationManager::load($record);
        $this->assertTrue($notification->payload['explicit']);
        $this->assertStringContainsString('notifies you about', $notification->getMailSubject());
        $this->assertStringContainsString('Space 2', $notification->getMailSubject());
        $this->assertMailSent(1);
    }

    public function testPostOnTheRecipientsProfileHasTheProfileWording()
    {
        // post 2: a post on Admin's profile, here by User1
        $notification = NotificationManager::fromRecord($this->createRecord(1, 2, Post::findOne(['id' => 2])));

        $this->assertStringContainsString('posted on your profile', $notification->asWeb());
        $this->assertStringContainsString('just wrote', $notification->getMailSubject());
    }

    public function testExplicitSubjectWithoutSpace()
    {
        $record = $this->createRecord(2, 1, Post::findOne(['id' => 2]), ['explicit' => true]);
        $subject = NotificationManager::fromRecord($record)->getMailSubject();

        $this->assertStringContainsString('notifies you about', $subject);
        $this->assertStringNotContainsString(' in ', $subject);
    }

    public function testGroupedSentenceAndSubject()
    {
        $first = $this->createRecord(2, 1, Post::findOne(['id' => 1]));
        $second = $this->createRecord(2, 1, Post::findOne(['id' => 2]));
        Notification::updateAll(['grouping_key' => $second->id], ['id' => [$first->id, $second->id]]);

        $row = Notification::find()->forUser(2)->grouped()->andWhere(['notification.class' => ContentCreatedNotification::class])->one();
        $notification = NotificationManager::load($row);
        $name = User::findOne(['id' => 1])->displayName;

        $this->assertSame(2, $notification->groupCount);
        $this->assertStringContainsString('created 2 new entries.', $notification->asWeb());
        $this->assertSame($name . ' created 2 new entries.', $notification->getMailSubject());
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
        $this->assertMailSent(1);
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
        $this->assertMailSent(1);
    }

    /**
     * Check that unauthorized explicit notify users are excluded for private profile content.
     */
    public function testExcludeUnauthorizedExplicitNotifyUserOnPrivateProfileContent()
    {
        $this->becomeUser('Admin');

        $post = new Post(['message' => 'MyTestContent']);
        $post->content->setContainer(User::findOne(['id' => 1]));
        $post->content->visibility = Content::VISIBILITY_PRIVATE;
        $post->content->notifyUsersOfNewContent = [User::findOne(['id' => 4])];
        $this->assertTrue($post->save());

        $this->assertMailSent(0);
        $this->assertHasNoNotification(ContentCreatedNotification::class, $post, null, 4);
    }

    /**
     * Check that notification preview is suppressed for unauthorized recipients.
     */
    public function testSuppressPreviewForUnauthorizedRecipient()
    {
        $this->becomeUser('Admin');

        $post = new Post(['message' => 'MyTestContent']);
        $post->content->setContainer(User::findOne(['id' => 1]));
        $post->content->visibility = Content::VISIBILITY_PRIVATE;
        $this->assertTrue($post->save());

        $recipient = User::findOne(['id' => 4]);
        NotificationManager::dispatch(ContentCreatedNotification::class, $recipient, $post, User::findOne(['id' => 1]));

        $this->assertHasNoNotification(ContentCreatedNotification::class, $post, null, $recipient->id);
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

        $this->assertMailSent(1);
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

        $this->assertMailSent(0);
    }

    /**
     * Test the deactivation of the mail target for new content.
     */
    public function testDeactivateMailNotificationAsSpaceMember()
    {
        $this->becomeUser('User3');

        // Disable $user1 notification settings.
        $this->disableContentMails(User::findOne(['id' => 2]));

        $post = new Post(['message' => 'MyTestContent']);
        $post->content->setContainer(Space::findOne(['id' => 3]));
        $post->content->visibility = Content::VISIBILITY_PUBLIC;
        $post->save();

        $this->assertMailSent(0);
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
        $this->assertMailSent(1);
    }

    /**
     * Admin and User2 are member of Space1 -> Space1 is set to default notification space.
     * Admin explicitly removes the default notification space.
     */
    public function testExplicitlyDisableDefaultNotificationSpace()
    {
        $this->becomeUser('Admin');

        Yii::$app->notification->setSpaces(['5396d499-20d6-4233-800b-c6c86e5fa34a']);

        // Saving the settings page without spaces
        $this->saveWithoutSpaces(User::findOne(['id' => 1]));

        $this->becomeUser('User2');

        $post = new Post(['message' => 'MyTestContent']);
        $post->content->setContainer(Space::findOne(['id' => 1]));
        $post->content->visibility = Content::VISIBILITY_PRIVATE;
        $post->save();

        // Note Admin is following Space2 so we expect one notification mail.
        $this->assertMailSent(0);
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
        $this->assertMailSent(4);
    }

    /**
     * Disable space as member and post public content.
     */
    public function testExplicitlyDisableDefaultNotificationPublicMember()
    {
        $this->becomeUser('Admin');

        Yii::$app->notification->setSpaces(['5396d499-20d6-4233-800b-c6c86e5fa34a']);

        // Saving the settings page without spaces
        $this->saveWithoutSpaces(User::findOne(['id' => 1]));

        $this->becomeUser('User2');

        $post = new Post(['message' => 'MyTestContent']);
        $post->content->setContainer(Space::findOne(['id' => 1]));
        $post->content->visibility = Content::VISIBILITY_PUBLIC;
        $post->save();

        // Note Admin is following Space2 so we expect one notification mail.
        $this->assertMailSent(3);
    }

    /**
     * Disable space as member and post public content.
     */
    public function testExplicitlyDisableDefaultNotificationPublic()
    {
        $this->becomeUser('User1');

        Yii::$app->notification->setSpaces(['5396d499-20d6-4233-800b-c6c86e5fa34a']);

        // Saving the settings page without spaces
        $this->saveWithoutSpaces(User::findOne(['id' => 2]));

        $this->becomeUser('User2');

        $post = new Post(['message' => 'MyTestContent']);
        $post->content->setContainer(Space::findOne(['id' => 1]));
        $post->content->visibility = Content::VISIBILITY_PUBLIC;
        $post->save();

        // Note Admin is following Space2 so we expect one notification mail.
        $this->assertMailSent(3);
    }

    /**
     * Disable space as member and post public content.
     */
    public function testExplicitlyDisableDefaultNotificationPublic2()
    {
        $this->becomeUser('User1');

        Yii::$app->notification->setSpaces(['5396d499-20d6-4233-800b-c6c86e5fa34a']);

        // Saving the settings page without spaces
        $this->saveWithoutSpaces(User::findOne(['id' => 2]));

        $this->becomeUser('Admin');

        Yii::$app->notification->setSpaces(['5396d499-20d6-4233-800b-c6c86e5fa34a']);

        // Saving the settings page without spaces
        $this->saveWithoutSpaces(User::findOne(['id' => 1]));

        $this->becomeUser('User2');

        $post = new Post(['message' => 'MyTestContent']);
        $post->content->setContainer(Space::findOne(['id' => 1]));
        $post->content->visibility = Content::VISIBILITY_PUBLIC;
        $post->save();

        // Note Admin is following Space2 so we expect one notification mail.
        $this->assertMailSent(2);
    }

    /**
     * What saving the settings page with no space selected does.
     */
    private function saveWithoutSpaces(User $user): void
    {
        $this->assertSame([], (new NotificationSettingsService($user))->fromArray(['spaces' => []]));
    }

    private function disableContentMails(User $user): void
    {
        (new NotificationSettingsService($user))->setGroup(
            Yii::$app->notification->getTarget('email'),
            NotificationGroup::content(),
            false,
        );
    }

    private function createRecord(int $userId, int $originatorId, Post $post, array $payload = []): Notification
    {
        $record = new Notification([
            'class' => ContentCreatedNotification::class,
            'user_id' => $userId,
            'originator_id' => $originatorId,
            'content_id' => $post->content->id,
            'contentcontainer_id' => $post->content->contentcontainer_id,
            'payload' => $payload,
        ]);
        $this->assertTrue($record->save());

        return $record;
    }
}
