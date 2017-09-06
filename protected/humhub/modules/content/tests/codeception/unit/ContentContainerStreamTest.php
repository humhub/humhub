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

    public function testPrivateContent()
    {
        $this->becomeUser('User2');
        $space = Space::findOne(['id' => 2]);

        $post1 = new Post;
        $post1->message = "Private Post";
        $post1->content->setContainer($space);
        $post1->content->visibility = Content::VISIBILITY_PRIVATE;
        $post1->save();
        $w1 = $post1->content->id;

        $post2 = new Post;
        $post2->message = "Public Post";
        $post2->content->setContainer($space);
        $post2->content->visibility = Content::VISIBILITY_PUBLIC;
        $post2->save();
        $w2 = $post2->content->id;

        $ids = $this->getStreamActionIds($space, 2);

        $this->assertTrue(in_array($w1, $ids));
        $this->assertTrue(in_array($w2, $ids));
    }

    public function testPublicContent()
    {
        $this->becomeUser('User2');
        $space = Space::findOne(['id' => 2]);

        $post1 = new Post;
        $post1->message = "Private Post";
        $post1->content->setContainer($space);
        $post1->content->visibility = Content::VISIBILITY_PRIVATE;
        $post1->save();
        $w1 = $post1->content->id;

        $post2 = new Post;
        $post2->message = "Public Post";
        $post2->content->setContainer($space);
        $post2->content->visibility = Content::VISIBILITY_PUBLIC;
        $post2->save();
        $w2 = $post2->content->id;


        $this->becomeUser('Admin');
        $ids = $this->getStreamActionIds($space, 2);

        $this->assertFalse(in_array($w1, $ids));
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

        $wallEntries = $action->activeQuery->all();

        $wallEntryIds = array_map(create_function('$entry', 'return $entry->id;'), $wallEntries);

        return $wallEntryIds;
    }
}
