<?php

namespace tests\codeception\unit\modules\content\components;

use Yii;
use yii\codeception\DbTestCase;
use Codeception\Specify;
use tests\codeception\fixtures\UserFixture;
use tests\codeception\fixtures\ContentContainerFixture;
use tests\codeception\fixtures\SpaceFixture;
use tests\codeception\fixtures\SpaceMembershipFixture;
use humhub\modules\post\models\Post;
use humhub\modules\content\components\actions\ContentContainerStream;
use humhub\modules\user\models\User;
use humhub\modules\space\models\Space;
use humhub\modules\content\models\Content;

class ContentContainerStreamTest extends DbTestCase
{

    use Specify;

    /**
     * @inheritdoc
     */
    public function fixtures()
    {
        return [
            'user' => [ 'class' => UserFixture::className()],
            'space' => [ 'class' => SpaceFixture::className()],
            'space_membership' => [ 'class' => SpaceMembershipFixture::className()],
            'contentcontainer' => [ 'class' => ContentContainerFixture::className()],
        ];
    }

    public function testPrivateContent()
    {
        $this->becomeUser('User2');
        $space = Space::findOne(['id' => 2]);

        $post1 = new Post;
        $post1->message = "Private Post";
        $post1->content->setContainer($space);
        $post1->content->visibility = Content::VISIBILITY_PRIVATE;
        $post1->save();
        $w1 = $post1->content->getFirstWallEntryId();

        $post2 = new Post;
        $post2->message = "Public Post";
        $post2->content->setContainer($space);
        $post2->content->visibility = Content::VISIBILITY_PUBLIC;
        $post2->save();
        $w2 = $post2->content->getFirstWallEntryId();

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
        $w1 = $post1->content->getFirstWallEntryId();

        $post2 = new Post;
        $post2->message = "Public Post";
        $post2->content->setContainer($space);
        $post2->content->visibility = Content::VISIBILITY_PUBLIC;
        $post2->save();
        $w2 = $post2->content->getFirstWallEntryId();


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

        $wallEntries = $action->getWallEntries();
        $wallEntryIds = array_map(create_function('$entry', 'return $entry->id;'), $wallEntries);

        return $wallEntryIds;
    }

    private function becomeUser($userName)
    {
        $user = User::findOne(['username' => $userName]);
        Yii::$app->user->switchIdentity($user);
    }

}
