<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\notification\tests\codeception\unit;

use humhub\helpers\Html;
use humhub\models\RecordMap;
use humhub\modules\comment\models\Comment;
use humhub\modules\notification\components\BaseNotification;
use humhub\modules\notification\components\NotificationBlock;
use humhub\modules\notification\components\NotificationCategory;
use humhub\modules\notification\components\NotificationContext;
use humhub\modules\notification\components\NotificationManager;
use humhub\modules\notification\components\NotificationPriority;
use humhub\modules\notification\models\Notification;
use humhub\modules\notification\targets\MailTarget;
use humhub\modules\notification\tests\codeception\unit\notifications\TestContentNotification;
use humhub\modules\notification\tests\codeception\unit\notifications\TestDefaultCategoryNotification;
use humhub\modules\notification\tests\codeception\unit\notifications\TestGroupedNotification;
use humhub\modules\notification\tests\codeception\unit\notifications\TestHighPriorityNotification;
use humhub\modules\notification\tests\codeception\unit\notifications\TestNotification;
use humhub\modules\notification\tests\codeception\unit\notifications\TestParamsNotification;
use humhub\modules\post\models\Post;
use humhub\modules\space\models\Space;
use humhub\modules\user\models\User;
use tests\codeception\_support\HumHubDbTestCase;
use Yii;
use yii\db\IntegrityException;
use yii\helpers\Json;

class BaseNotificationTest extends HumHubDbTestCase
{
    public $fixtureConfig = ['default'];

    public function testLoadsTheRecordAndRendersPerChannel()
    {
        $post = Post::findOne(['id' => 1]);
        $record = new Notification([
            'class' => TestContentNotification::class,
            'user_id' => 1,
            'originator_id' => 2,
            'content_id' => $post->content->id,
            'contentcontainer_id' => $post->content->contentcontainer_id,
        ]);
        $this->assertTrue($record->save());

        $n = NotificationManager::load($record);
        $this->assertInstanceOf(TestContentNotification::class, $n);
        $this->assertSame(1, $n->recipient->id);
        $this->assertSame(2, $n->originator->id);
        $this->assertSame($post->content->id, $n->content->id);
        $this->assertSame($post->content->contentcontainer_id, $n->contentContainer->id);
        $this->assertNull($n->sourceRecord);
        $this->assertSame(1, $n->groupCount);
        $this->assertSame([], $n->payload);

        $this->assertStringContainsString('<strong>', $n->asWeb());
        $this->assertStringContainsString('<strong>', $n->asMailHtml());
        $this->assertStringNotContainsString('<', $n->asMailText());
        $this->assertStringNotContainsString('<', $n->asPush());
        $this->assertStringContainsString($n->originator->displayName, $n->asPush());
        $this->assertStringContainsString($n->originator->displayName, $n->asMailSubject());
        $this->assertStringNotContainsString('<', $n->asMailSubject());

        $this->assertStringContainsString('notification/entry', urldecode($n->getEntryUrl()));
        $this->assertStringContainsString('id=' . $record->id, urldecode($n->getEntryUrl()));
        $this->assertStringStartsWith('http', $n->getEntryUrl());
        $this->assertSame($post->content->getUrl(), $n->getUrl());
        $this->assertSame($post->content->getUrl(true), $n->getUrl(true));
        // the default blocks: the preview of the content and the View online button
        $blocks = $n->getBlocks(new NotificationContext(MailTarget::ID));
        $this->assertCount(2, $blocks);
        $this->assertSame(NotificationBlock::TYPE_CONTENT_PREVIEW, $blocks[0]->getType());
        $this->assertSame($post->id, $blocks[0]->getRecord()->id);
        $this->assertEquals(NotificationBlock::button(Yii::t('NotificationModule.base', 'View online'), $n->getEntryUrl()), $blocks[1]);
        $this->assertEquals($blocks, $n->getRenderedBlocks(new NotificationContext(MailTarget::ID)));
        $this->assertFalse(TestContentNotification::standalone());
        $this->assertTrue($n->canReceive(User::findOne(['id' => 1])));
        $this->assertNull(TestContentNotification::grouping());
    }

    public function testGoneContentRecordRendersDeleted()
    {
        $post = $this->createPost('Soon gone');
        $record = new Notification([
            'class' => TestContentNotification::class,
            'user_id' => 1,
            'originator_id' => 2,
            'content_id' => $post->content->id,
            'contentcontainer_id' => $post->content->contentcontainer_id,
        ]);
        $this->assertTrue($record->save());

        // the post's row goes without events, the content row stays
        Post::deleteAll(['id' => $post->id]);

        $n = NotificationManager::load(Notification::findOne(['id' => $record->id]));
        $this->assertStringEndsWith('created [Deleted]', $n->asWeb());
        $this->assertStringEndsWith('created [Deleted]', $n->asMailText());
        $this->assertEquals([NotificationBlock::button('View online', $n->getEntryUrl())], $n->getBlocks(new NotificationContext(MailTarget::ID)));
    }

    public function testSourceRecordThroughRecordMap()
    {
        $comment = $this->createComment();
        $record = new Notification([
            'class' => TestNotification::class,
            'user_id' => 1,
            'originator_id' => 2,
            'content_id' => $comment->content->id,
            'source_record_id' => RecordMap::getId($comment),
        ]);
        $this->assertTrue($record->save());
        $n = NotificationManager::load($record);
        $this->assertSame($comment->id, $n->sourceRecord->id);
        // a comment is a content addon: its own URL wins over the content's
        $this->assertSame($comment->getUrl(), $n->getUrl());
    }

    public function testGoneSourceRecordIsAnIntegrityError()
    {
        $comment = $this->createComment();
        $record = new Notification(['class' => TestNotification::class, 'user_id' => 1, 'source_record_id' => RecordMap::getId($comment)]);
        $record->save();
        // delete the comment row without events, so the record_map row stays behind
        Comment::deleteAll(['id' => $comment->id]);
        // the comment's notifications resolved it through the per-request record map cache
        Yii::$app->runtimeCache->flush();
        $this->expectException(IntegrityException::class);
        NotificationManager::load($record);
    }

    public function testUnknownClassIsAnIntegrityError()
    {
        $record = new Notification(['class' => 'humhub\modules\notification\DoesNotExist', 'user_id' => 1]);
        $record->save();
        $this->expectException(IntegrityException::class);
        NotificationManager::load($record);
    }

    public function testPayloadPriorityAndListingDefaults()
    {
        $record = new Notification(['class' => TestNotification::class, 'user_id' => 1, 'payload' => Json::encode(['count' => 3])]);
        $this->assertTrue($record->save());
        $n = NotificationManager::load($record);
        $this->assertSame(['count' => 3], $n->payload);
        $this->assertSame(NotificationPriority::Low, TestNotification::priority());
        $this->assertSame(NotificationPriority::Normal, TestContentNotification::priority());
        $this->assertTrue(TestNotification::listed());
    }

    public function testWithoutOriginator()
    {
        $record = new Notification(['class' => TestNotification::class, 'user_id' => 1]);
        $record->save();
        $n = NotificationManager::load($record);
        $this->assertNull($n->originator);
        $this->assertNull($n->getUrl());
        $this->assertSame(' did a thing', $n->asMailText());
    }

    public function testLoadsTheNewestMemberOfAGroup()
    {
        $post = Post::findOne(['id' => 1]);
        $a = new Notification(['class' => TestGroupedNotification::class, 'user_id' => 1, 'originator_id' => 2, 'content_id' => $post->content->id]);
        $a->save();
        $b = new Notification(['class' => TestGroupedNotification::class, 'user_id' => 1, 'originator_id' => 3, 'content_id' => $post->content->id]);
        $b->save();
        Notification::updateAll(['grouping_key' => $b->id], ['id' => [$a->id, $b->id]]);

        $head = Notification::find()->forUser(1)->grouped()->andWhere(['notification.grouping_key' => $b->id])->one();
        $n = NotificationManager::load($head);
        $this->assertSame($b->id, $n->record->id);
        $this->assertSame(2, $n->groupCount);
        $this->assertSame(3, $n->originator->id);
        $newest = User::findOne(['id' => 3])->displayName;
        $other = User::findOne(['id' => 2])->displayName;
        $this->assertStringContainsString($newest, $n->asMailText());
        $this->assertStringContainsString($other, $n->asMailText());
        $this->assertStringContainsString(
            Yii::t('NotificationModule.base', '{displayName1} and {displayName2}', ['displayName1' => $newest, 'displayName2' => $other]),
            $n->asMailText(),
        );
        $this->assertStringContainsString('did 2 things', $n->asMailText());
    }

    public function testMailTextAndSubjectArePlainText()
    {
        $post = $this->createPost('Let\'s go & say "hi" <now>');

        $record = new Notification([
            'class' => TestContentNotification::class,
            'user_id' => 1,
            'originator_id' => 2,
            'content_id' => $post->content->id,
            'contentcontainer_id' => $post->content->contentcontainer_id,
        ]);
        $this->assertTrue($record->save());
        $n = NotificationManager::load($record);

        $this->assertStringContainsString('Let\'s go & say "hi" <now>', $n->asMailText());
        $this->assertStringContainsString('Let\'s go & say "hi" <now>', $n->asMailSubject());
        $this->assertStringContainsString(Html::encode('"hi"'), $n->asMailHtml());
        $this->assertStringContainsString('Let\'s go & say "hi" <now>', $n->asPush());
        $this->assertStringNotContainsString('&quot;', $n->asMailSubject() . $n->asPush());
    }

    public function testPushAndSubjectCarryTheShortPreview()
    {
        $long = str_repeat('Lorem ipsum dolor sit amet ', 10);
        $post = $this->createPost($long);
        $record = new Notification(['class' => TestContentNotification::class, 'user_id' => 1, 'originator_id' => 2, 'content_id' => $post->content->id]);
        $this->assertTrue($record->save());
        $n = NotificationManager::load($record);

        $this->assertStringContainsString(trim($long), $n->asMailText());
        $this->assertSame($n->asPush(), $n->asMailSubject());
        $this->assertLessThan(mb_strlen($n->asMailText()), mb_strlen($n->asMailSubject()));
    }

    public function testPriorityAndListingOverrides()
    {
        $this->assertSame(NotificationPriority::High, TestHighPriorityNotification::priority());
        $this->assertSame(NotificationPriority::Low, TestHighPriorityNotification::category()->priority);
        $this->assertFalse(TestHighPriorityNotification::listed());
    }

    public function testContainerOnlyNotification()
    {
        $space = Space::findOne(['id' => 1]);
        $record = new Notification(['class' => TestNotification::class, 'user_id' => 1, 'originator_id' => 2, 'contentcontainer_id' => $space->contentcontainer_id]);
        $this->assertTrue($record->save());
        $n = NotificationManager::load($record);
        $this->assertEquals([NotificationBlock::button('View online', $n->getEntryUrl())], $n->getBlocks(new NotificationContext(MailTarget::ID)));
        $this->assertSame($space->getUrl(), $n->getUrl());
    }

    public function testClassThatIsNoNotificationIsAnIntegrityError()
    {
        $record = new Notification(['class' => Notification::class, 'user_id' => 1]);
        $record->save();
        $this->expectException(IntegrityException::class);
        NotificationManager::load($record);
    }

    public function testAndMoreCountsAllOtherOriginators()
    {
        // four others besides the recipient (1) and the newest originator (2); user 5 is disabled,
        // so not named, but still counted
        $n = $this->createGroup([3, 4, 8, 5, 2]);
        $this->assertSame(5, $n->groupCount);
        $this->assertStringContainsString(Yii::t('NotificationModule.base', '{displayName1}, {displayName2} and {count} more', [
            'displayName1' => User::findOne(['id' => 2])->displayName,
            'displayName2' => User::findOne(['id' => 8])->displayName,
            'count' => 3,
        ]), $n->asMailText());
        $this->assertStringContainsString('<strong>' . Html::encode(User::findOne(['id' => 2])->displayName) . '</strong>', $n->asWeb());
        $this->assertStringContainsString('<strong>' . Html::encode(User::findOne(['id' => 8])->displayName) . '</strong>', $n->asWeb());
    }

    public function testRecipientIsNeverNamed()
    {
        $n = $this->createGroup([1, 3, 2]);
        $this->assertStringContainsString(Yii::t('NotificationModule.base', '{displayName1} and {displayName2}', [
            'displayName1' => User::findOne(['id' => 2])->displayName,
            'displayName2' => User::findOne(['id' => 3])->displayName,
        ]), $n->asMailText());
        $this->assertStringNotContainsString(User::findOne(['id' => 1])->displayName, $n->asMailText());
    }

    public function testGroupWithoutOriginatorNamesTheOthersOnly()
    {
        $n = $this->createGroup([3, null]);
        $this->assertNull($n->originator);
        $this->assertSame(User::findOne(['id' => 3])->displayName . ' did 2 things', $n->asMailText());
        $this->assertStringStartsWith('<strong>', $n->asWeb());
        $this->assertStringNotContainsString('<strong></strong>', $n->asWeb());
    }

    public function testPayloadGivenAsArray()
    {
        $record = new Notification(['class' => TestNotification::class, 'user_id' => 1, 'payload' => ['a' => 1]]);
        $this->assertTrue($record->save());
        $n = NotificationManager::load(Notification::findOne(['id' => $record->id]));
        $this->assertSame(['a' => 1], $n->payload);
    }

    public function testCategoryDefaultsToTheModule()
    {
        $category = TestDefaultCategoryNotification::category();
        $this->assertEquals(NotificationCategory::ofModule(TestDefaultCategoryNotification::class), $category);
        $this->assertSame('notification', $category->id);
        $this->assertSame(NotificationPriority::Normal, TestDefaultCategoryNotification::priority());
        $this->assertContains('notification', array_map(
            fn(NotificationCategory $category) => $category->id,
            (new class (['targets' => []]) extends NotificationManager {
                protected function findNotificationClasses(): array
                {
                    return [TestDefaultCategoryNotification::class];
                }
            })->getCategories(),
        ));
    }

    public function testExtraParamsAreRenderedPerChannel()
    {
        $record = new Notification(['class' => TestParamsNotification::class, 'user_id' => 1, 'originator_id' => 2, 'payload' => ['title' => 'A & <B>', 'note' => 'x < y']]);
        $this->assertTrue($record->save());
        $n = NotificationManager::load($record);
        $name = User::findOne(['id' => 2])->displayName;

        $this->assertSame('<strong>' . Html::encode($name) . '</strong> shared <strong>A &amp; &lt;B&gt;</strong> (x &lt; y)', $n->asWeb());
        $this->assertSame($n->asWeb(), $n->asMailHtml());
        $this->assertSame($name . ' shared “A & <B>” (x < y)', $n->asMailText());
        $this->assertSame($n->asMailText(), $n->asPush());
        $this->assertSame($n->asMailText(), $n->asMailSubject());
    }

    /**
     * A group of {@see TestGroupedNotification}s to user 1 about post 1, one per originator in
     * creation order; the loaded group head.
     */
    private function createGroup(array $originatorIds): BaseNotification
    {
        $post = Post::findOne(['id' => 1]);
        $ids = [];
        foreach ($originatorIds as $originatorId) {
            $record = new Notification(['class' => TestGroupedNotification::class, 'user_id' => 1, 'originator_id' => $originatorId, 'content_id' => $post->content->id]);
            $this->assertTrue($record->save());
            $ids[] = $record->id;
        }
        Notification::updateAll(['grouping_key' => end($ids)], ['id' => $ids]);

        return NotificationManager::load(Notification::find()->forUser(1)->grouped()->andWhere(['notification.grouping_key' => end($ids)])->one());
    }

    /**
     * A post of User1 on their profile.
     */
    private function createPost(string $message): Post
    {
        $this->becomeUser('User1');
        $post = new Post(['message' => $message]);
        $post->content->setContainer(User::findOne(['id' => 2]));
        $this->assertTrue($post->save());

        return $post;
    }

    /**
     * A comment of User1 on post 1.
     */
    private function createComment(): Comment
    {
        $this->becomeUser('User1');
        $comment = new Comment(['message' => 'Test comment', 'content_id' => Post::findOne(['id' => 1])->content->id]);
        $this->assertTrue($comment->save());

        return $comment;
    }
}
