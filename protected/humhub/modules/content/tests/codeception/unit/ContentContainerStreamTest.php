<?php

namespace tests\codeception\unit\modules\content;

use humhub\modules\stream\models\filters\DefaultStreamFilter;
use Yii;
use tests\codeception\_support\HumHubDbTestCase;
use humhub\modules\post\models\Post;

use humhub\modules\space\models\Space;
use humhub\modules\content\models\Content;
use humhub\modules\stream\actions\ContentContainerStream;

class ContentContainerStreamTest extends HumHubDbTestCase
{

    /**
     * @var Space
     */
    private $space;

    public function _before()
    {
        parent::_before();
        $this->space = Space::findOne(['id' => 2]);
    }

    public function testPrivateContent()
    {
        $this->becomeUser('User2');

        $w1 = $this->createPublicPost();
        $w2 = $this->createPrivatePost();

        $ids = $this->getStreamActionIds($this->space, 2);

        $this->assertTrue(in_array($w1, $ids));
        $this->assertTrue(in_array($w2, $ids));
    }

    public function testPrivateContentAsAdminNotMemberCannotViewAllContent()
    {
        $this->becomeUser('User2');

        $w1 = $this->createPrivatePost();
        $w2 = $this->createPublicPost();

        $this->becomeUser('AdminNotMember');
        $ids = $this->getStreamActionIds($this->space, 2);

        $this->assertFalse(in_array($w1, $ids));
        $this->assertTrue(in_array($w2, $ids));
    }

    public function testPrivateContentAsAdminNotMemberCanViewAllContent()
    {
        $this->becomeUser('User2');

        $w1 = $this->createPrivatePost();
        $w2 = $this->createPublicPost();

        Yii::$app->getModule('content')->adminCanViewAllContent = true;
        $this->becomeUser('AdminNotMember');
        $ids = $this->getStreamActionIds($this->space, 2);

        $this->assertTrue(in_array($w1, $ids));
        $this->assertTrue(in_array($w2, $ids));
    }

    public function testPublicContent()
    {
        $this->becomeUser('User2');

        $w1 = $this->createPrivatePost();
        $w2 = $this->createPublicPost();

        $this->becomeUser('Admin');
        $ids = $this->getStreamActionIds($this->space, 2);

        $this->assertFalse(in_array($w1, $ids));
        $this->assertTrue(in_array($w2, $ids));
    }

    public function testPublicContentAsAdminCanViewAllContent()
    {
        $this->becomeUser('User2');

        $w1 = $this->createPrivatePost();
        $w2 = $this->createPublicPost();

        Yii::$app->getModule('content')->adminCanViewAllContent = true;
        $this->becomeUser('Admin');
        $ids = $this->getStreamActionIds($this->space, 2);

        $this->assertTrue(in_array($w1, $ids));
        $this->assertTrue(in_array($w2, $ids));
    }

    public function testDraftContent()
    {
        $this->becomeUser('User2');
        $draft1Id = $this->createPost('Some Draft', ['visibility' => Content::VISIBILITY_PRIVATE, 'state' => Content::STATE_DRAFT]);
        $regular1Id = $this->createPost('Regular 1 by U2', ['visibility' => Content::VISIBILITY_PRIVATE]);
        $this->becomeUser('Admin');
        $regular2Id = $this->createPost('Regular 2 by Admin', ['visibility' => Content::VISIBILITY_PRIVATE]);

        $this->becomeUser('User2');
        $ids = $this->getStreamActionIds($this->space, 2);

        // Check draft is first for Author
        $this->assertTrue($ids[0] === $draft1Id);

        // Check draft is not visible for other users
        $this->becomeUser('Admin');
        $ids = $this->getStreamActionIds($this->space, 5);
        $this->assertTrue(!in_array($draft1Id, $ids));
    }

    public function testHiddenContent()
    {
        $this->becomeUser('User2');

        $hiddenPostId = $this->createPost('Hidden Post', ['hidden' => 1]);
        $visiblePostId = $this->createPost('Regular Post');

        // Not in Stream
        $ids = $this->getStreamActionIds($this->space, 2);
        $this->assertTrue($ids[0] === $visiblePostId);
        $this->assertFalse($ids[1] === $hiddenPostId);

        // Single Stream Entry Request
        $hiddenPostId2 = $this->createPost('Hidden Post 2', ['hidden' => 1]);
        $ids = $this->getStreamActionIds($this->space, 1);
        $this->assertTrue($ids[0] === $hiddenPostId2);

        // Show Hidden Only Filter
        $ids = $this->getStreamActionIds($this->space, 2, [DefaultStreamFilter::FILTER_HIDDEN]);
        $this->assertTrue($ids[0] === $hiddenPostId2);
        $this->assertTrue($ids[1] === $hiddenPostId);
    }


    public function testDeletedContent()
    {
        $this->becomeUser('User2');
        $deleteId = $this->createPost('Something to delete',['visibility' => Content::VISIBILITY_PRIVATE]);

        $post = Post::findOne(['id' => $deleteId]);
        $post->content->softDelete();

        $ids = $this->getStreamActionIds($this->space, 3);

        // Deleted Content should not appear in stream
        $this->assertTrue(!in_array($deleteId, $ids));
    }

    private function getStreamActionIds($container, $limit = 4, $filters = []): array
    {
        $action = new ContentContainerStream('stream', Yii::$app->controller, [
            'contentContainer' => $container,
            'limit' => $limit,
            'filters' => $filters
        ]);

        $wallEntries = $action->getStreamQuery()->all();

        $wallEntryIds = array_map(static function ($entry) {
            return $entry->id;
        }, $wallEntries);

        return $wallEntryIds;
    }

    private function createPrivatePost()
    {
        return $this->createPost('Private Post', ['visibility' => Content::VISIBILITY_PRIVATE]);
    }

    private function createPublicPost()
    {
        return $this->createPost('Public Post', ['visibility' => Content::VISIBILITY_PUBLIC]);
    }

    private function createPost($message, $content = [])
    {
        if (!isset($content['visibility'])) {
            $content['visibility'] = Content::VISIBILITY_PRIVATE;
        }
        if (!isset($content['state'])) {
            $content['state'] = Content::STATE_PUBLISHED;
        }

        $post = new Post;
        $post->message = $message;
        $post->content->setContainer($this->space);
        $post->content->setAttributes($content, false);
        $post->save();

        return $post->content->id;
    }
}
