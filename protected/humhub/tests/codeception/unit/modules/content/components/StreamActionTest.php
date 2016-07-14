<?php

namespace tests\codeception\unit\modules\content\components;

use Yii;
use yii\codeception\DbTestCase;
use Codeception\Specify;
use tests\codeception\fixtures\UserFixture;
use tests\codeception\fixtures\GroupFixture;
use tests\codeception\fixtures\SpaceFixture;
use tests\codeception\fixtures\SpaceMembershipFixture;
use tests\codeception\fixtures\WallFixture;
use tests\codeception\fixtures\WallEntryFixture;
use tests\codeception\fixtures\ContentFixture;
use tests\codeception\fixtures\PostFixture;
use humhub\modules\post\models\Post;
use humhub\modules\content\components\actions\Stream;
use humhub\modules\user\models\User;
use humhub\modules\activity\models\Activity;

class StreamActionTest extends DbTestCase
{

    use Specify;

    /**
     * @inheritdoc
     */
    public function fixtures()
    {
        return [
            'user' => [
                'class' => UserFixture::className(),
            ],
            'group' => [
                'class' => GroupFixture::className(),
            ],
            'space' => [
                'class' => SpaceFixture::className(),
            ],
            'space_membership' => [
                'class' => SpaceMembershipFixture::className(),
            ],
            'wall' => [
                'class' => WallFixture::className(),
            ],
            'wall_entry' => [
                'class' => WallEntryFixture::className(),
            ],
            'content' => [
                'class' => ContentFixture::className(),
            ],
            'post' => [
                'class' => PostFixture::className(),

            ],
        ];
    }

    private $postWallEntryIds = array();

    protected function setUp()
    {
        parent::setUp();

        $user1 = User::findOne(['id' => 1]);
        Yii::$app->user->setIdentity($user1);


        $post = new Post;
        $post->message = "P1";
        $post->content->container = Yii::$app->user->getIdentity();
        $post->save();
        $this->postWallEntryIds[] = $post->content->getFirstWallEntryId();

        $post = new Post;
        $post->message = "P2";
        $post->content->container = Yii::$app->user->getIdentity();
        $post->save();
        $this->postWallEntryIds[] = $post->content->getFirstWallEntryId();

        $post = new Post;
        $post->message = "P3";
        $post->content->container = Yii::$app->user->getIdentity();
        $post->save();
        $this->postWallEntryIds[] = $post->content->getFirstWallEntryId();

        $post = new Post;
        $post->message = "P4";
        $post->content->container = Yii::$app->user->getIdentity();
        $post->save();
        $this->postWallEntryIds[] = $post->content->getFirstWallEntryId();

        $this->postWallEntryIds = array_reverse($this->postWallEntryIds);
    }

    public function testModeNormal()
    {
        Yii::$app->user->switchIdentity(User::findOne(['id' => 1]));
        $streamAction = new Stream('stream', Yii::$app->controller);
        $streamAction->init();

        $wallEntries = $streamAction->getWallEntries();
        $wallEntryIds = array_map(create_function('$entry', 'return $entry->id;'), $wallEntries);

        $this->assertEquals($this->postWallEntryIds, $wallEntryIds);
    }

    public function testModeActivity()
    {
        Yii::$app->user->switchIdentity(User::findOne(['id' => 2]));

        // Post of User 2 - should not be included in activities
        $post = new Post;
        $post->message = "P5";
        $post->content->setContainer(Yii::$app->user->getIdentity());
        $post->save();

        $streamAction = new Stream('stream', Yii::$app->controller);
        $streamAction->mode = Stream::MODE_ACTIVITY;
        $streamAction->init();

        $wallEntries = $streamAction->getWallEntries();

        assert(count($wallEntries) == 4);

        foreach ($wallEntries as $entry) {
            assert(($entry->content->object_model == Activity::className() && $entry->content->created_by != 2));
        }
    }

    public function testStreamDisabledUser()
    {
        Yii::$app->user->switchIdentity(User::findOne(['id' => 2]));

        // Post of User 2 - should not be included thus he is deactivated
        $post = new Post;
        $post->message = "P5";
        $post->content->setContainer(Yii::$app->user->getIdentity());
        $post->save();

        $user = User::findOne(['id' => 2]);
        $user->status = User::STATUS_DISABLED;
        $user->save();

        $baseStreamAction = new Stream('stream', Yii::$app->controller);
        $baseStreamAction->mode = Stream::MODE_NORMAL;
        $baseStreamAction->init();

        $wallEntries = $baseStreamAction->getWallEntries();
        $wallEntryIds = array_map(create_function('$entry', 'return $entry->id;'), $wallEntries);
        $this->assertEquals($this->postWallEntryIds, $wallEntryIds);
    }

    public function testOrder()
    {

        /**
         * @todo FIXME, change time in database instead of sleeping
         */
        sleep(1);
        $post1 = new Post;
        $post1->message = "P1";
        $post1->content->setContainer(Yii::$app->user->getIdentity());
        $post1->save();
        $post1wallEntryId = $post1->content->getFirstWallEntryId();
        sleep(1);
        $post2 = new Post;
        $post2->message = "P2";
        $post2->content->setContainer(Yii::$app->user->getIdentity());
        $post2->save();
        $post2wallEntryId = $post2->content->getFirstWallEntryId();
        sleep(1);
        $post1->message = "P1b";
        $post1->save();

        $baseStreamAction = new Stream('stream', Yii::$app->controller);
        $baseStreamAction->limit = 2;
        $baseStreamAction->init();
        $wallEntries = $baseStreamAction->getWallEntries();
        $wallEntryIds = array_map(create_function('$entry', 'return $entry->id;'), $wallEntries);

        $this->assertEquals(array($post2wallEntryId, $post1wallEntryId), $wallEntryIds);

        $baseStreamAction = new Stream('stream', Yii::$app->controller);
        $baseStreamAction->limit = 2;
        $baseStreamAction->sort = Stream::SORT_UPDATED_AT;

        $baseStreamAction->init();
        $wallEntries = $baseStreamAction->getWallEntries();
        $wallEntryIds = array_map(create_function('$entry', 'return $entry->id;'), $wallEntries);

        $this->assertEquals(array($post1wallEntryId, $post2wallEntryId), $wallEntryIds);
    }

    public function testFrom()
    {
        // Test From Sorting of Stream
    }

    public function testLimit()
    {
        $baseStreamAction = new Stream('stream', Yii::$app->controller);
        $baseStreamAction->limit = 2;
        $baseStreamAction->init();

        $wallEntries = $baseStreamAction->getWallEntries();
        $wallEntryIds = array_map(create_function('$entry', 'return $entry->id;'), $wallEntries);
        $this->assertEquals(array_slice($this->postWallEntryIds, 0, 2), $wallEntryIds);
    }

}
