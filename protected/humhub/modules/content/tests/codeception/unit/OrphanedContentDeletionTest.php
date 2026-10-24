<?php

namespace tests\codeception\unit\modules\content;

use humhub\modules\content\Events;
use humhub\modules\content\jobs\PurgeDeletedContents;
use humhub\modules\content\models\Content;
use humhub\modules\post\models\Post;
use humhub\modules\space\models\Space;
use humhub\modules\user\events\UserEvent;
use tests\codeception\_support\HumHubDbTestCase;
use Yii;
use yii\base\Event;

class OrphanedContentDeletionTest extends HumHubDbTestCase
{
    public function testPurgeDeletedContentsHandlesOrphanedContent()
    {
        $content = $this->createOrphanedContent();
        $content->updateAttributes(['state' => Content::STATE_DELETED]);

        (new PurgeDeletedContents())->run();

        $this->assertNull(Content::findOne(['id' => $content->id]));
    }

    public function testUserDeleteHandlesOrphanedContent()
    {
        $content = $this->createOrphanedContent();

        Events::onUserDelete(new Event(['sender' => Yii::$app->user->getIdentity()]));

        $this->assertNull(Content::findOne(['id' => $content->id]));
    }

    public function testUserSoftDeleteHandlesOrphanedContent()
    {
        $content = $this->createOrphanedContent(true);

        Events::onUserSoftDelete(new UserEvent(['user' => Yii::$app->user->getIdentity()]));

        $this->assertNull(Content::findOne(['id' => $content->id]));
    }

    private function createOrphanedContent(bool $onOwnProfile = false): Content
    {
        $this->becomeUser('User2');

        $post = new Post(['message' => 'Orphaned content test post']);
        $post->content->setContainer($onOwnProfile ? Yii::$app->user->getIdentity() : Space::findOne(['id' => 2]));
        $post->save();

        // Simulate an inconsistent state: the underlying record is gone
        // while its content entry was left behind
        Post::deleteAll(['id' => $post->id]);

        return Content::findOne(['id' => $post->content->id]);
    }
}
