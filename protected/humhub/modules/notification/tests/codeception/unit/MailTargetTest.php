<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\notification\tests\codeception\unit;

use humhub\helpers\Html;
use humhub\modules\notification\components\BaseNotification;
use humhub\modules\notification\components\NotificationBlock;
use humhub\modules\notification\components\NotificationCategory;
use humhub\modules\notification\components\NotificationManager;
use humhub\modules\notification\models\Notification;
use humhub\modules\notification\services\NotificationSettingsService;
use humhub\modules\notification\targets\DeliveryBatch;
use humhub\modules\notification\targets\MailTarget;
use humhub\modules\notification\tests\codeception\unit\notifications\TestContentNotification;
use humhub\modules\notification\tests\codeception\unit\notifications\TestDirectNotification;
use humhub\modules\notification\tests\codeception\unit\notifications\TestBlocksNotification;
use humhub\modules\notification\tests\codeception\unit\notifications\TestMultilineNotification;
use humhub\modules\notification\tests\codeception\unit\notifications\TestNotification;
use humhub\modules\post\models\Post;
use humhub\modules\user\models\User;
use tests\codeception\_support\HumHubDbTestCase;
use Yii;
use yii\helpers\Url;
use yii\symfonymailer\Message;

/**
 * The queue of the test application is synchronous and its configuration makes the channels
 * instant (`delays = [0]`, no low-priority delay): a dispatch sends its mails at once, one per
 * notification. The timings of the delivery layer are tested in {@see DeliverySchedulerTest}.
 */
class MailTargetTest extends HumHubDbTestCase
{
    public $fixtureConfig = ['default'];

    protected function tearDown(): void
    {
        TestBlocksNotification::reset();
        parent::tearDown();
    }

    public function testDispatchSendsOneMail()
    {
        $this->dispatch(TestContentNotification::class);

        $mails = $this->mails();
        $this->assertCount(1, $mails);
        $mail = $mails[0];
        $this->assertSame(['admin@example.com'], array_keys($mail->getTo()));

        $notification = $this->notification(TestContentNotification::class);
        $this->assertSame($notification->asMailSubject(), $mail->getSubject());

        $html = $mail->getSymfonyEmail()->getHtmlBody();
        $this->assertStringContainsString($notification->asMailHtml(), $html);
        $this->assertStringContainsString(Html::encode($notification->getEntryUrl()), $html);
        // the default block, the preview of the post, and the action button
        $this->assertStringContainsString(Html::encode(Post::findOne(['id' => 2])->message), $html);
        $this->assertStringContainsString('View online', $html);

        $text = $mail->getSymfonyEmail()->getTextBody();
        $this->assertStringContainsString($notification->asMailText(), $text);
        $this->assertStringContainsString(Post::findOne(['id' => 2])->message, $text);
        $this->assertStringContainsString('View online: ' . $notification->getEntryUrl(), $text);
    }

    public function testCategorySwitchedOffSendsNothing()
    {
        (new NotificationSettingsService($this->recipient()))->setCategory(new MailTarget(), NotificationCategory::content(), false);
        $this->dispatch(TestContentNotification::class);

        $this->assertCount(0, $this->mails());
    }

    public function testDirectCategoryIgnoresItsSwitch()
    {
        Yii::$app->getModule('notification')->settings->user($this->recipient())->set('email.category.direct', 0);
        $this->dispatch(TestDirectNotification::class);

        $this->assertCount(1, $this->mails());
    }

    public function testRecipientWithoutEmailGetsNoMail()
    {
        User::updateAll(['email' => null], ['id' => 1]);
        static::logInitialize();

        $this->dispatch(TestContentNotification::class);

        $this->assertCount(1, Notification::findAll(['class' => TestContentNotification::class]));
        $this->assertCount(0, $this->mails());
        static::assertLogRegexCount(0, '/delivery through email failed/', null, ['notification']);
    }

    public function testLocaleOfTheRecipientIsRestored()
    {
        $language = Yii::$app->language;
        User::updateAll(['language' => 'de'], ['id' => 1]);

        $this->dispatch(TestContentNotification::class);

        $mails = $this->mails();
        $this->assertCount(1, $mails);
        // rendered in the recipient's language
        $this->assertStringContainsString('Abbestellen', $mails[0]->getSymfonyEmail()->getTextBody());
        $this->assertSame($language, Yii::$app->language);
        $this->assertArrayNotHasKey('showUnsubscribe', Yii::$app->view->params);
    }

    public function testInvalidAddressIsLoggedAndTheOthersStillGetTheirMail()
    {
        User::updateAll(['email' => 'not-an-address'], ['id' => 1]);
        static::logInitialize();

        TestContentNotification::send([1, 3], Post::findOne(['id' => 2]), User::findOne(['id' => 2]));

        static::assertLogRegexCount(1, '/delivery through email failed/', null, ['notification']);
        $mails = $this->mails();
        $this->assertCount(1, $mails);
        $this->assertSame(['user2@example.com'], array_keys($mails[0]->getTo()));
    }

    public function testMultilineSubjectIsOneLine()
    {
        $this->dispatch(TestMultilineNotification::class);

        $mails = $this->mails();
        $this->assertCount(1, $mails);
        $this->assertSame('Peter Tester wrote two lines', $mails[0]->getSubject());
    }

    public function testBatchOfSeveralNotifications()
    {
        // stop the dispatch from mailing, then deliver both in one batch
        $this->stopMailing();
        $this->dispatch(TestContentNotification::class);
        $this->dispatch(TestNotification::class);
        // a post in space 1, of which the recipient is a member
        $spacePost = Post::find()->joinWith('content')->andWhere(['content.contentcontainer_id' => 4])->one();
        TestContentNotification::send([1], $spacePost, User::findOne(['id' => 2]));
        $this->assertCount(0, $this->mails());

        $notifications = array_map(
            NotificationManager::fromRecord(...),
            Notification::find()->andWhere(['user_id' => 1])->orderBy(['id' => SORT_ASC])->all(),
        );
        $this->assertCount(3, $notifications);
        $batch = new DeliveryBatch($this->recipient(), $notifications, 'email');
        $this->assertFalse($batch->isSingle());
        $this->assertSame('3 new notifications', $batch->getSubject());
        $this->assertSame('3 new notifications', $batch->getPushBody());
        $this->assertSame(Url::to(['/notification/overview'], true), $batch->getUrl());
        $this->assertSame('email:1', $batch->getCollapseKey());
        $this->assertFalse($batch->isHighPriority());
        $this->assertGreaterThanOrEqual(2, $batch->getUnreadCount());

        (new MailTarget())->deliver($batch);

        $mails = $this->mails();
        $this->assertCount(1, $mails);
        $this->assertSame('3 new notifications', $mails[0]->getSubject());
        $html = $mails[0]->getSymfonyEmail()->getHtmlBody();
        $text = $mails[0]->getSymfonyEmail()->getTextBody();
        foreach ($notifications as $notification) {
            $this->assertStringContainsString($notification->asMailHtml(), $html);
            $this->assertStringContainsString(Html::encode($notification->getEntryUrl()), $html);
            $this->assertStringContainsString($notification->asMailText(), $text);
            $this->assertStringContainsString($notification->getEntryUrl(), $text);
        }
        // the notification about the space post under the space's heading, after the others
        $this->assertGreaterThan(strpos($html, (string) Html::encode($notifications[1]->getEntryUrl())), strpos($html, 'Space 1'));
        $this->assertLessThan(strpos($html, (string) Html::encode($notifications[2]->getEntryUrl())), strpos($html, 'Space 1'));
        $this->assertStringContainsString(Html::encode(Url::to(['/notification/overview'], true)), $html);
        $this->assertStringContainsString(Url::to(['/notification/overview'], true), $text);
    }

    public function testSingleBatch()
    {
        $this->stopMailing();
        $this->dispatch(TestDirectNotification::class);

        $notification = $this->notification(TestDirectNotification::class);
        $batch = new DeliveryBatch($this->recipient(), [$notification], 'mobile');
        $this->assertTrue($batch->isSingle());
        $this->assertSame($notification->asPush(), $batch->getPushBody());
        $this->assertSame($notification->getEntryUrl(), $batch->getUrl());
        $this->assertSame('mobile:1:' . (int)$notification->record->grouping_key, $batch->getCollapseKey());
        $this->assertTrue($batch->isHighPriority());
    }

    public function testSingleMailShowsTheBlocks()
    {
        TestBlocksNotification::$testBlocks = static fn(): array => [
            NotificationBlock::heading('Release <2.0>'),
            NotificationBlock::text("Let <b>me</b> in!\nPlease"),
            NotificationBlock::html('<span class="custom">Custom</span>', 'Custom alternative'),
            NotificationBlock::button('Confirm <reading>', 'https://example.com/confirm?a=1&b=2'),
            NotificationBlock::button('Later', 'https://example.com/later'),
        ];
        TestBlocksNotification::send([1], originator: User::findOne(['id' => 2]));

        $mails = $this->mails();
        $this->assertCount(1, $mails);
        $html = $mails[0]->getSymfonyEmail()->getHtmlBody();
        $text = $mails[0]->getSymfonyEmail()->getTextBody();

        $this->assertStringContainsString('Release &lt;2.0&gt;', $html);
        $this->assertStringContainsString('Let &lt;b&gt;me&lt;/b&gt; in!<br />', $html);
        $this->assertStringNotContainsString('<b>me</b>', $html);
        $this->assertStringContainsString('<span class="custom">Custom</span>', $html);
        $this->assertStringNotContainsString('Custom alternative', $html);
        $this->assertStringContainsString('Confirm &lt;reading&gt;', $html);
        $this->assertStringContainsString('href="https://example.com/confirm?a=1&amp;b=2"', $html);
        // the consecutive buttons in one row, no View online button appended
        $this->assertSame(1, substr_count($html, 'Confirm &lt;reading&gt;'));
        $this->assertDoesNotMatchRegularExpression('/Confirm &lt;reading&gt;.*<td height="15"><\/td>.*Later/s', $html);
        $this->assertStringNotContainsString('View online', $html);
        // sentence, then the blocks in their order
        $this->assertLessThan(strpos($html, 'Release &lt;2.0&gt;'), strpos($html, 'Peter Tester'));
        $this->assertLessThan(strpos($html, 'Confirm &lt;reading&gt;'), strpos($html, 'Custom</span>'));

        $this->assertStringContainsString(
            "Peter Tester wrote you\n\nRelease <2.0>\n\nLet <b>me</b> in!\nPlease\n\nCustom alternative\n\n"
            . "Confirm <reading>: https://example.com/confirm?a=1&b=2\nLater: https://example.com/later\n",
            $text,
        );
        $this->assertStringNotContainsString('<span', $text);
        $this->assertStringNotContainsString('View online', $text);

        // the HTML and the text part each asked once, with the mail context
        $this->assertCount(2, TestBlocksNotification::$contexts);
        foreach (TestBlocksNotification::$contexts as $context) {
            $this->assertSame(MailTarget::ID, $context->channel);
        }
    }

    public function testViewOnlineButtonIsAppendedWithoutButtons()
    {
        TestBlocksNotification::$testBlocks = static fn(): array => [NotificationBlock::text('Only text')];
        // post 2: public post on the admin's profile, so the notification has a URL
        TestBlocksNotification::send([1], source: Post::findOne(['id' => 2]), originator: User::findOne(['id' => 2]));

        $mails = $this->mails();
        $this->assertCount(1, $mails);
        $notification = $this->notification(TestBlocksNotification::class);
        $html = $mails[0]->getSymfonyEmail()->getHtmlBody();
        $this->assertStringContainsString('View online', $html);
        $this->assertLessThan(strpos($html, 'View online'), strpos($html, 'Only text'));
        $this->assertStringContainsString("Only text\n\nView online: " . $notification->getEntryUrl(), $mails[0]->getSymfonyEmail()->getTextBody());
    }

    public function testNoButtonWithoutUrl()
    {
        TestBlocksNotification::$testBlocks = static fn(): array => [NotificationBlock::text('Only text')];
        TestBlocksNotification::send([1], originator: User::findOne(['id' => 2]));

        $mails = $this->mails();
        $this->assertCount(1, $mails);
        $this->assertStringContainsString('Only text', $mails[0]->getSymfonyEmail()->getHtmlBody());
        $this->assertStringNotContainsString('View online', $mails[0]->getSymfonyEmail()->getHtmlBody());
        $this->assertStringNotContainsString('View online', $mails[0]->getSymfonyEmail()->getTextBody());
    }

    public function testBatchMailShowsNoBlocks()
    {
        $this->stopMailing();
        $this->dispatch(TestNotification::class);
        TestBlocksNotification::$testBlocks = static fn(): array => [NotificationBlock::text('Hidden block')];
        TestBlocksNotification::send([1], originator: User::findOne(['id' => 2]));

        $notifications = array_map(
            NotificationManager::fromRecord(...),
            Notification::find()->andWhere(['user_id' => 1])->orderBy(['id' => SORT_ASC])->all(),
        );
        $this->assertCount(2, $notifications);
        (new MailTarget())->deliver(new DeliveryBatch($this->recipient(), $notifications, 'email'));

        $mails = $this->mails();
        $this->assertCount(1, $mails);
        $this->assertStringContainsString('Peter Tester wrote you', $mails[0]->getSymfonyEmail()->getTextBody());
        $this->assertStringNotContainsString('Hidden block', $mails[0]->getSymfonyEmail()->getHtmlBody());
        $this->assertStringNotContainsString('Hidden block', $mails[0]->getSymfonyEmail()->getTextBody());
        $this->assertStringNotContainsString('View online', $mails[0]->getSymfonyEmail()->getHtmlBody());
        $this->assertSame([], TestBlocksNotification::$contexts);
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

    /**
     * Keeps the dispatch from mailing (the records are still written for the web list), so a test
     * can deliver the batch itself.
     */
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

    /**
     * @return Message[]
     */
    private function mails(): array
    {
        return $this->getYiiModule()->grabSentEmails();
    }
}
