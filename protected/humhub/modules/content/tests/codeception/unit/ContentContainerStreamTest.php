<?php

namespace tests\codeception\unit\modules\content;

use humhub\modules\admin\permissions\ManageAllContent;
use humhub\modules\content\models\Content;
use humhub\modules\post\models\Post;
use humhub\modules\space\models\Space;
use humhub\modules\content\actions\ContentContainerStream;
use humhub\modules\content\models\stream\filters\DefaultStreamFilter;
use humhub\modules\content\models\stream\filters\TopicStreamFilter;
use humhub\modules\topic\models\Topic;
use tests\codeception\_support\HumHubDbTestCase;
use Yii;

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

    public function testManageAllContentPermission()
    {
        $this->becomeUser('User1');
        $w1 = $this->createPrivatePost();
        $w2 = $this->createPublicPost();

        $this->becomeUser('User2');
        $ids = $this->getStreamActionIds($this->space, 2);
        $this->assertFalse(in_array($w1, $ids));
        $this->assertTrue(in_array($w2, $ids));
        $this->becomeUser('Admin');
        $ids = $this->getStreamActionIds($this->space, 2);
        $this->assertFalse(in_array($w1, $ids));
        $this->assertTrue(in_array($w2, $ids));
        $this->becomeUser('AdminNotMember');
        $ids = $this->getStreamActionIds($this->space, 2);
        $this->assertFalse(in_array($w1, $ids));
        $this->assertTrue(in_array($w2, $ids));

        Yii::$app->getModule('admin')->enableManageAllContentPermission = true;

        $this->becomeUser('User2');
        $ids = $this->getStreamActionIds($this->space, 2);
        $this->assertFalse(in_array($w1, $ids));
        $this->assertTrue(in_array($w2, $ids));
        $this->becomeUser('Admin');
        $ids = $this->getStreamActionIds($this->space, 2);
        $this->assertTrue(in_array($w1, $ids)); // Manage Content permission is enabled by default for admins
        $this->assertTrue(in_array($w2, $ids));
        $this->becomeUser('AdminNotMember');
        $ids = $this->getStreamActionIds($this->space, 2);
        $this->assertTrue(in_array($w1, $ids)); // Manage Content permission is enabled by default for admins
        $this->assertTrue(in_array($w2, $ids));

        self::setGroupPermission(3, new ManageAllContent());
        $this->becomeUser('User2');
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

    public function testHiddenContentWithTopicFilter()
    {
        $this->becomeUser('User2');

        $topic = new Topic($this->space);
        $topic->name = 'Wiki';
        $this->assertTrue($topic->save());

        $hiddenPostId = $this->createPost('Hidden Post with Topic', ['hidden' => 1]);
        $visiblePostId = $this->createPost('Regular Post with Topic');
        $this->createPost('Hidden Post without Topic', ['hidden' => 1]);
        Topic::attach(Content::findOne(['id' => $hiddenPostId]), [$topic]);
        Topic::attach(Content::findOne(['id' => $visiblePostId]), [$topic]);

        // A topic filter includes hidden content
        $ids = $this->getStreamActionIds($this->space, 4, [], [$topic->id]);
        $this->assertSame([$visiblePostId, $hiddenPostId], $ids);

        // The same with the topic filter of a stream request (stream filters load request data only in web requests)
        Yii::$app->request->setIsConsoleRequest(false);
        Yii::$app->request->setQueryParams(['StreamQuery' => ['topics' => [(string)$topic->id]]]);
        try {
            $ids = $this->getStreamActionIds($this->space);
        } finally {
            Yii::$app->request->setQueryParams([]);
            Yii::$app->request->setIsConsoleRequest(null);
        }
        $this->assertSame([$visiblePostId, $hiddenPostId], $ids);

        // Show Hidden Only Filter
        $ids = $this->getStreamActionIds($this->space, 4, [DefaultStreamFilter::FILTER_HIDDEN], [$topic->id]);
        $this->assertSame([$hiddenPostId], $ids);
    }


    public function testDeletedContent()
    {
        $this->becomeUser('User2');
        $deleteId = $this->createPost('Something to delete', ['visibility' => Content::VISIBILITY_PRIVATE]);

        $content = Content::findOne(['id' => $deleteId]);
        $content->softDelete();

        $ids = $this->getStreamActionIds($this->space, 3);

        // Deleted Content should not appear in stream
        $this->assertTrue(!in_array($deleteId, $ids));
    }

    private function getStreamActionIds($container, $limit = 4, $filters = [], $topics = []): array
    {
        $action = new ContentContainerStream('stream', Yii::$app->controller, [
            'contentContainer' => $container,
            'limit' => $limit,
            'filters' => $filters,
            'filterHandlers' => $topics ? [new TopicStreamFilter(['topics' => $topics])] : [],
        ]);

        $wallEntries = $action->getStreamQuery()->all();

        $wallEntryIds = array_map(static fn($entry) => $entry->id, $wallEntries);

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

        $post = new Post();
        $post->message = $message;
        $post->content->setContainer($this->space);
        $post->content->setAttributes($content, false);
        $post->save();

        return $post->content->id;
    }
}
