<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\notification\tests\codeception\unit;

use humhub\modules\comment\notifications\NewCommentNotification;
use humhub\modules\content\notifications\ContentCreatedNotification;
use humhub\modules\like\notifications\NewLikeNotification;
use humhub\modules\notification\components\BaseNotification;
use humhub\modules\notification\components\NotificationManager;
use humhub\modules\notification\models\Notification;
use humhub\modules\post\models\Post;
use humhub\modules\user\models\User;
use humhub\modules\user\notifications\FollowedNotification;
use tests\codeception\_support\HumHubDbTestCase;

/**
 * The single ICU messages of the core notifications whose sentence differs by grouping only.
 */
class CoreMessagesTest extends HumHubDbTestCase
{
    public $fixtureConfig = ['default'];

    public function testNewLike()
    {
        $this->assertStringStartsWith($this->name(2) . ' likes ', $this->load(NewLikeNotification::class, [2])->asMailText());
        $grouped = $this->load(NewLikeNotification::class, [3, 2]);
        $this->assertStringStartsWith($this->name(2) . ' and ' . $this->name(3) . ' like ', $grouped->asMailText());
        $this->assertStringStartsWith($this->name(2) . ' and ' . $this->name(3) . ' like your ', $grouped->asMailSubject());
        $this->assertStringStartsWith('<strong>', $grouped->asWeb());
    }

    public function testNewComment()
    {
        $this->assertStringStartsWith($this->name(2) . ' commented ', $this->load(NewCommentNotification::class, [2])->asMailText());
        $this->assertStringStartsWith(
            $this->name(2) . ' and ' . $this->name(3) . ' commented ',
            $this->load(NewCommentNotification::class, [3, 2])->asMailText(),
        );
    }

    public function testFollowed()
    {
        $this->assertSame($this->name(2) . ' is now following you.', $this->load(FollowedNotification::class, [2], false)->asMailText());
        $this->assertSame(
            $this->name(2) . ' and ' . $this->name(3) . ' are now following you.',
            $this->load(FollowedNotification::class, [3, 2], false)->asMailText(),
        );
    }

    public function testContentCreated()
    {
        $this->assertStringStartsWith($this->name(2) . ' created ', $this->load(ContentCreatedNotification::class, [2])->asMailText());
        $this->assertSame($this->name(2) . ' created 2 new entries.', $this->load(ContentCreatedNotification::class, [2, 2])->asMailText());
    }

    private function name(int $userId): string
    {
        return User::findOne(['id' => $userId])->displayName;
    }

    /**
     * Notifications of the class to user 1 about post 1, one per originator in creation order,
     * grouped when more than one; the loaded group head.
     *
     * @param int[] $originatorIds
     */
    private function load(string $class, array $originatorIds, bool $withContent = true): BaseNotification
    {
        $content = Post::findOne(['id' => 1])->content;
        $ids = [];
        foreach ($originatorIds as $originatorId) {
            $record = new Notification([
                'class' => $class,
                'user_id' => 1,
                'originator_id' => $originatorId,
                'content_id' => $withContent ? $content->id : null,
            ]);
            $this->assertTrue($record->save());
            $ids[] = $record->id;
        }
        Notification::updateAll(['grouping_key' => end($ids)], ['id' => $ids]);

        return NotificationManager::load(
            Notification::find()->forUser(1)->grouped()->andWhere(['notification.grouping_key' => end($ids)])->one(),
        );
    }
}
