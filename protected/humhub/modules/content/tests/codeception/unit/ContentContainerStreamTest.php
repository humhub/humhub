<?php

namespace tests\codeception\unit\modules\content;

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

    private function getStreamActionIds($container, $limit = 4)
    {
        $action = new ContentContainerStream('stream', Yii::$app->controller, [
            'contentContainer' => $container,
            'limit' => $limit
        ]);

        $action->contentContainer = $container;
        $action->limit = $limit;

        $wallEntries = $action->getStreamQuery()->all();

        $wallEntryIds = array_map(static function($entry) {return $entry->id; }, $wallEntries);

        return $wallEntryIds;
    }

    private function createPrivatePost()
    {
        return $this->createPost('Private Post', Content::VISIBILITY_PRIVATE);
    }

    private function createPublicPost()
    {
        return $this->createPost('Public Post', Content::VISIBILITY_PUBLIC);
    }

    private function createPost($message, $visibility)
    {
        $post = new Post;
        $post->message = $message;
        $post->content->setContainer($this->space);
        $post->content->visibility = $visibility;
        $post->save();

        return $post->content->id;
    }
}
