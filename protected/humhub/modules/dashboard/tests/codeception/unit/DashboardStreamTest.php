<?php

namespace dashboard\unit;

use Yii;
use tests\codeception\_support\HumHubDbTestCase;
use Codeception\Specify;
use humhub\modules\post\models\Post;
use humhub\modules\dashboard\components\actions\DashboardStream;
use humhub\modules\user\models\User;
use humhub\modules\space\models\Space;

use humhub\modules\content\models\Content;

class DashboardStreamTest extends HumHubDbTestCase
{

    use Specify;

    /**
     * if a user follows another user, the public posts are included
     * the private not
     */
    public function testUserFollow()
    {
        $this->becomeUser('User1');

        $post1 = new Post();
        $post1->message = "Private Post";
        $post1->content->container = Yii::$app->user->getIdentity();
        $post1->content->visibility = Content::VISIBILITY_PRIVATE;
        $post1->save();
        
        $w1 = $post1->content->id;

        $post2 = new Post();
        $post2->message = "Public Post";
        $post2->content->container = Yii::$app->user->getIdentity();
        $post2->content->visibility = Content::VISIBILITY_PUBLIC;
        $post2->save();
        
        $w2 = $post2->content->id;

        $this->becomeUser('Admin');
        $ids = $this->getStreamActionIds(2);
        
        $this->assertFalse(in_array($w1, $ids));
        $this->assertTrue(in_array($w2, $ids));
    }

    /**
     * if a user follows a space is the PUBLIC  post included
     * the private not
    */
    public function testSpaceFollow()
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
        $ids = $this->getStreamActionIds(2);

        $this->assertFalse(in_array($w1, $ids));
        $this->assertTrue(in_array($w2, $ids));
    }

    /**
     * When member of a space, public & private content should returned.
     * When no member no content should be returned.
    */
    public function testSpaceMembership()
    {
        $this->becomeUser('Admin');
        $space = Space::findOne(['id' => 1]);

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
        
        $this->assertEquals($this->getStreamActionIds(2), [$w2, $w1]);

        $this->becomeUser('User3');
        $ids = $this->getStreamActionIds(2);
        $this->assertFalse(in_array($w1, $ids));
        $this->assertFalse(in_array($w2, $ids));
    }

    /**
     * Own profile content should appear with visibility Private & Public
    */
    public function testOwnContent()
    {
        $this->becomeUser('Admin');

        $post1 = new Post;
        $post1->message = "Own Private Post";
        $post1->content->container = Yii::$app->user->getIdentity();
        $post1->content->visibility = Content::VISIBILITY_PRIVATE;
        $post1->save();
        $w1 = $post1->content->id;

        $post2 = new Post;
        $post2->message = "Own Public Post";
        $post2->content->container = Yii::$app->user->getIdentity();
        $post2->content->visibility = Content::VISIBILITY_PUBLIC;
        $post2->save();
        $w2 = $post2->content->id;

        $ids = $this->getStreamActionIds(2);
        $this->assertEquals($ids, array($w2, $w1));
    }

    private function getStreamActionIds($limit = 4)
    {
        $action = new DashboardStream('stream', Yii::$app->controller, [
            'limit' => $limit,
        ]);
        
        $action->init();
        
        $streamEntries = $action->activeQuery->all();
        return array_map(create_function('$entry', 'return $entry->id;'), $streamEntries);
    }
}
