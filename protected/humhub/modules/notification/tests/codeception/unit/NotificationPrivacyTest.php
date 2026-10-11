<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\notification\tests\codeception\unit;

use humhub\components\message\MessageFormat;
use humhub\helpers\Html;
use humhub\modules\notification\components\BaseNotification;
use humhub\modules\notification\components\NotificationBlock;
use humhub\modules\notification\components\NotificationManager;
use humhub\modules\notification\components\NotificationPrivacy;
use humhub\modules\notification\models\Notification;
use humhub\modules\notification\Module;
use humhub\modules\notification\targets\DeliveryBatch;
use humhub\modules\notification\targets\MailTarget;
use humhub\modules\notification\tests\codeception\unit\notifications\TestBlocksNotification;
use humhub\modules\notification\tests\codeception\unit\notifications\TestContentNotification;
use humhub\modules\notification\tests\codeception\unit\notifications\TestContentTitleNotification;
use humhub\modules\notification\tests\codeception\unit\notifications\TestNotification;
use humhub\modules\notification\tests\codeception\unit\notifications\TestRedactedNotification;
use humhub\modules\post\models\Post;
use humhub\modules\user\models\User;
use tests\codeception\_support\HumHubDbTestCase;
use Yii;
use yii\base\InvalidConfigException;
use yii\helpers\Url;
use yii\symfonymailer\Message;

/**
 * The privacy mode of mails and push ({@see NotificationPrivacy}): what a single mail, a mail of
 * several notifications and a push message show per level.
 */
class NotificationPrivacyTest extends HumHubDbTestCase
{
    public $fixtureConfig = ['default'];

    /**
     * The content of post 2, which the notifications are about.
     */
    private const CONTENT = 'User 1 Profile Post Public';

    protected function tearDown(): void
    {
        TestBlocksNotification::reset();
        TestRedactedNotification::$redactedParams = [];
        $this->module()->privacyMode = null;
        unset(Yii::$app->params['fixed-settings']['notification']);
        parent::tearDown();
    }

    public function testFullDetailsByDefault()
    {
        $this->assertSame(NotificationPrivacy::Full, NotificationPrivacy::current());
        $this->assertFalse($this->module()->isPrivacyFixed());
        $this->assertFalse(NotificationPrivacy::Full->isProtected());
        $this->assertTrue(NotificationPrivacy::Redacted->isProtected());
        $this->assertTrue(NotificationPrivacy::Minimal->isProtected());
    }

    public function testTheSettingIsStored()
    {
        $this->module()->setPrivacy(NotificationPrivacy::Minimal);

        $this->assertSame('minimal', $this->module()->settings->get(Module::SETTING_PRIVACY_MODE));
        $this->assertSame(NotificationPrivacy::Minimal, NotificationPrivacy::current());
    }

    public function testTheConfigurationWinsOverTheSetting()
    {
        $this->module()->setPrivacy(NotificationPrivacy::Minimal);
        $this->module()->privacyMode = 'redacted';

        $this->assertSame(NotificationPrivacy::Redacted, NotificationPrivacy::current());
        $this->assertTrue($this->module()->isPrivacyFixed());

        // the setting cannot be changed meanwhile
        $this->module()->setPrivacy(NotificationPrivacy::Full);
        $this->assertSame('minimal', $this->module()->settings->get(Module::SETTING_PRIVACY_MODE));
    }

    public function testAFixedSettingIsReadOnly()
    {
        Yii::$app->params['fixed-settings']['notification'][Module::SETTING_PRIVACY_MODE] = 'minimal';

        $this->assertSame(NotificationPrivacy::Minimal, NotificationPrivacy::current());
        $this->assertTrue($this->module()->isPrivacyFixed());
    }

    public function testAnInvalidConfigurationFails()
    {
        $this->module()->privacyMode = 'none';

        $this->expectException(InvalidConfigException::class);
        NotificationPrivacy::current();
    }

    public function testTheBatchAndItsContextCarryTheLevel()
    {
        $this->stopMailing();
        $this->dispatch(TestNotification::class);
        $this->module()->setPrivacy(NotificationPrivacy::Redacted);

        $batch = new DeliveryBatch($this->recipient(), [$this->notification(TestNotification::class)], MailTarget::ID);
        $this->assertSame(NotificationPrivacy::Redacted, $batch->privacy);
        $this->assertSame(NotificationPrivacy::Redacted, $batch->getContext()->privacy);
        $this->assertSame(MailTarget::ID, $batch->getContext()->channel);

        $explicit = new DeliveryBatch($this->recipient(), [$this->notification(TestNotification::class)], MailTarget::ID, NotificationPrivacy::Minimal);
        $this->assertSame(NotificationPrivacy::Minimal, $explicit->getContext()->privacy);
    }

    public function testRedactedSingleMailShowsTheSentenceWithTheContentType()
    {
        $this->module()->setPrivacy(NotificationPrivacy::Redacted);
        $this->dispatch(TestContentNotification::class);

        [$mail] = $this->mails(1);
        $notification = $this->notification(TestContentNotification::class);
        $this->assertSame('Peter Tester created post', $mail->getSubject());

        $html = $mail->getSymfonyEmail()->getHtmlBody();
        $text = $mail->getSymfonyEmail()->getTextBody();
        $this->assertStringContainsString('<strong>Peter Tester</strong> created post', $html);
        $this->assertStringContainsString("Peter Tester created post\n\nView online: " . $notification->getEntryUrl(), $text);
        $this->assertNoContent($html, $text);
    }

    public function testRedactedSingleMailFallsBackToTheCategory()
    {
        $this->module()->setPrivacy(NotificationPrivacy::Redacted);
        $this->dispatch(TestContentTitleNotification::class);

        [$mail] = $this->mails(1);
        $notification = $this->notification(TestContentTitleNotification::class);
        $this->assertSame('Peter Tester: New content in my Spaces', $mail->getSubject());

        $html = $mail->getSymfonyEmail()->getHtmlBody();
        $text = $mail->getSymfonyEmail()->getTextBody();
        $this->assertStringContainsString('<strong>Peter Tester</strong>: New content in my Spaces', $html);
        $this->assertStringContainsString("Peter Tester: New content in my Spaces\n\nView online: " . $notification->getEntryUrl(), $text);
        $this->assertStringContainsString(Html::encode($notification->getEntryUrl()), $html);
        $this->assertNoContent($html, $text);
    }

    public function testRedactedMessageGetsNoContentParams()
    {
        $this->module()->setPrivacy(NotificationPrivacy::Redacted);
        $this->dispatch(TestRedactedNotification::class);

        [$mail] = $this->mails(1);
        $this->assertSame('Peter Tester shared a post with Sara Tester', $mail->getSubject());
        $this->assertSame(
            ['displayName', 'displayNames', 'namedCount', 'groupCount', 'contentType', 'other'],
            array_keys(TestRedactedNotification::$redactedParams),
        );

        $html = $mail->getSymfonyEmail()->getHtmlBody();
        $text = $mail->getSymfonyEmail()->getTextBody();
        $this->assertStringContainsString('<strong>Peter Tester</strong> shared a post with <strong>Sara Tester</strong>', $html);
        $this->assertStringContainsString('Peter Tester shared a post with Sara Tester', $text);
        $this->assertNoContent($html, $text, $mail->getSubject());

        $batch = new DeliveryBatch($this->recipient(), [$this->notification(TestRedactedNotification::class)], 'mobile');
        $this->assertSame('Peter Tester shared a post with Sara Tester', $batch->getPushBody());
    }

    public function testRedactedMailShowsNoBlocks()
    {
        $this->module()->setPrivacy(NotificationPrivacy::Redacted);
        TestBlocksNotification::$testBlocks = static fn(): array => [
            NotificationBlock::text('Secret block'),
            NotificationBlock::button('Confirm reading', 'https://example.com/confirm'),
        ];
        // post 2: the notification has a URL
        TestBlocksNotification::send([1], source: Post::findOne(['id' => 2]), originator: User::findOne(['id' => 2]));

        [$mail] = $this->mails(1);
        $html = $mail->getSymfonyEmail()->getHtmlBody();
        $text = $mail->getSymfonyEmail()->getTextBody();
        foreach ([$html, $text] as $body) {
            $this->assertStringNotContainsString('Secret block', $body);
            $this->assertStringNotContainsString('Confirm reading', $body);
            $this->assertStringContainsString('View online', $body);
        }
        // the blocks are not even asked for
        $this->assertSame([], TestBlocksNotification::$contexts);
    }

    public function testRedactedMailWithoutUrlHasNoButton()
    {
        $this->module()->setPrivacy(NotificationPrivacy::Redacted);
        TestBlocksNotification::send([1], originator: User::findOne(['id' => 2]));

        [$mail] = $this->mails(1);
        $this->assertStringNotContainsString('View online', $mail->getSymfonyEmail()->getHtmlBody());
        $this->assertStringNotContainsString('View online', $mail->getSymfonyEmail()->getTextBody());
    }

    public function testRedactedBatchMailHasNoSpaceHeadings()
    {
        $notifications = $this->batchNotifications();
        $this->module()->setPrivacy(NotificationPrivacy::Redacted);

        $batch = new DeliveryBatch($this->recipient(), $notifications, MailTarget::ID);
        $this->assertSame('3 new notifications', $batch->getSubject());
        $this->assertSame('3 new notifications', $batch->getPushBody());
        (new MailTarget())->deliver($batch);

        [$mail] = $this->mails(1);
        $html = $mail->getSymfonyEmail()->getHtmlBody();
        $text = $mail->getSymfonyEmail()->getTextBody();
        foreach ($notifications as $notification) {
            $this->assertStringContainsString($notification->asRedactedHtml(), $html);
            $this->assertStringContainsString(Html::encode($notification->getEntryUrl()), $html);
            $this->assertStringContainsString($notification->asRedactedText() . "\n" . $notification->getEntryUrl(), $text);
        }
        $this->assertStringContainsString("Peter Tester created post\n", $text);
        $this->assertStringContainsString("Peter Tester did a thing\n", $text);
        $this->assertNoContent($html, $text);
        $this->assertStringNotContainsString('Space 1', $html);
        $this->assertStringNotContainsString('Space 1', $text);
    }

    public function testMinimalSingleMailNamesTheCategoryOnly()
    {
        $this->module()->setPrivacy(NotificationPrivacy::Minimal);
        $this->dispatch(TestRedactedNotification::class);

        [$mail] = $this->mails(1);
        $notification = $this->notification(TestRedactedNotification::class);
        $this->assertSame('New notification: New content in my Spaces', $mail->getSubject());

        $html = $mail->getSymfonyEmail()->getHtmlBody();
        $text = $mail->getSymfonyEmail()->getTextBody();
        $this->assertStringContainsString("New notification: New content in my Spaces\n\nView online: " . $notification->getEntryUrl(), $text);
        $this->assertStringContainsString(Html::encode($notification->getEntryUrl()), $html);
        $this->assertNoContent($html, $text);
        $this->assertNoNames($html, $text);
        // the class's sentence without content is not used either
        $this->assertSame([], TestRedactedNotification::$redactedParams);

        $batch = new DeliveryBatch($this->recipient(), [$notification], 'mobile');
        $this->assertSame('1 new notification', $batch->getPushBody());
        $this->assertSame('New notification: New content in my Spaces', $batch->getSentence($notification, MessageFormat::Html));
        // the collapse key does not change
        $this->assertSame('mobile:1:' . (int)$notification->record->grouping_key, $batch->getCollapseKey());
    }

    public function testMinimalBatchMailCountsPerCategory()
    {
        $notifications = $this->batchNotifications();
        $this->module()->setPrivacy(NotificationPrivacy::Minimal);

        $batch = new DeliveryBatch($this->recipient(), $notifications, MailTarget::ID);
        $this->assertSame([
            'New content in my Spaces: 2 new notifications',
            'Reactions on my content: 1 new notification',
        ], $batch->getCategorySummary());
        $this->assertSame('3 new notifications', $batch->getSubject());
        $this->assertSame('3 new notifications', $batch->getPushBody());
        (new MailTarget())->deliver($batch);

        [$mail] = $this->mails(1);
        $html = $mail->getSymfonyEmail()->getHtmlBody();
        $text = $mail->getSymfonyEmail()->getTextBody();
        $this->assertStringContainsString('New content in my Spaces: 2 new notifications', $html);
        $this->assertStringContainsString(
            "New content in my Spaces: 2 new notifications\nReactions on my content: 1 new notification\n\n"
            . 'Open notifications: ' . Url::to(['/notification/overview'], true),
            $text,
        );
        $this->assertNoContent($html, $text);
        $this->assertNoNames($html, $text);
        foreach ($notifications as $notification) {
            $this->assertStringNotContainsString(Html::encode($notification->getEntryUrl()), $html);
        }
        $this->assertStringNotContainsString('Space 1', $html);
    }

    public function testFullDetailsAreUnchanged()
    {
        $this->dispatch(TestRedactedNotification::class);

        [$mail] = $this->mails(1);
        $this->assertStringContainsString('Secret title', $mail->getSubject());
        $this->assertStringContainsString(self::CONTENT, $mail->getSymfonyEmail()->getTextBody());
        $this->assertSame([], TestRedactedNotification::$redactedParams);
    }

    /**
     * Three notifications of user 1 that are not mailed: two of the content category - one about
     * post 2, one about a post in Space 1 - and one of the social category.
     *
     * @return BaseNotification[]
     */
    private function batchNotifications(): array
    {
        $this->stopMailing();
        $this->dispatch(TestContentNotification::class);
        $this->dispatch(TestNotification::class);
        $spacePost = Post::find()->joinWith('content')->andWhere(['content.contentcontainer_id' => 4])->one();
        TestRedactedNotification::send([1], $spacePost, User::findOne(['id' => 2]));

        $notifications = array_map(
            NotificationManager::fromRecord(...),
            Notification::find()->andWhere(['user_id' => 1])->orderBy(['id' => SORT_ASC])->all(),
        );
        $this->assertCount(3, $notifications);

        return $notifications;
    }

    private function assertNoContent(string ...$bodies): void
    {
        foreach ($bodies as $body) {
            $this->assertStringNotContainsString(self::CONTENT, $body);
            $this->assertStringNotContainsString('Profile Post', $body);
            $this->assertStringNotContainsString('Secret title', $body);
            $this->assertStringNotContainsString('Secret note', $body);
        }
    }

    private function assertNoNames(string ...$bodies): void
    {
        foreach ($bodies as $body) {
            $this->assertStringNotContainsString('Peter Tester', $body);
            $this->assertStringNotContainsString('Sara Tester', $body);
        }
    }

    /**
     * @param class-string<BaseNotification> $class
     */
    private function dispatch(string $class): void
    {
        // post 2: public post on the admin's profile
        $class::send([1], Post::findOne(['id' => 2]), User::findOne(['id' => 2]));
    }

    private function notification(string $class): BaseNotification
    {
        return NotificationManager::fromRecord(Notification::findOne(['class' => $class, 'user_id' => 1]));
    }

    private function stopMailing(): void
    {
        foreach (Yii::$app->notification->getTargets() as $target) {
            if ($target instanceof MailTarget) {
                $target->active = false;
            }
        }
    }

    private function recipient(): User
    {
        return User::findOne(['id' => 1]);
    }

    private function module(): Module
    {
        return Yii::$app->getModule('notification');
    }

    /**
     * @return Message[]
     */
    private function mails(int $expected): array
    {
        $mails = $this->getYiiModule()->grabSentEmails();
        $this->assertCount($expected, $mails);

        return $mails;
    }
}
