<?php


namespace modules\user\tests\codeception\unit\stream;


use humhub\modules\content\models\Content;
use humhub\modules\post\models\Post;
use humhub\modules\space\models\Space;
use humhub\modules\user\tests\codeception\_support\ProfileStreamTest;
use yii\base\Exception;

class PinnedContentStreamTest extends ProfileStreamTest
{
    /**
     * Make sure profile owner sees public space contributions
     * @throws Exception
     */
    public function testPinnedProfilePostIsFirst()
    {
        $post1 = $this->createProfilePost(Content::VISIBILITY_PUBLIC, 'First');
        $post2 = $this->createProfilePost(Content::VISIBILITY_PUBLIC, 'Second');
        $post3 = $this->createProfilePost(Content::VISIBILITY_PUBLIC, 'Third');

        $this->pinPost($post2);

        $result = $this->createQuery()->all();

        $this->assertCount(3, $result);
        $this->assertEquals($post2->content->id, $result[0]->id);
        $this->assertEquals($post3->content->id, $result[1]->id);
        $this->assertEquals($post1->content->id, $result[2]->id);
    }

    /**
     * Make sure profile owner sees public space contributions
     * @throws Exception
     */
    public function testMultiplePinnedProfilePostsAreFirst()
    {
        $post1 = $this->createProfilePost(Content::VISIBILITY_PUBLIC, 'First');
        $post2 = $this->createProfilePost(Content::VISIBILITY_PUBLIC, 'Second');
        $post3 = $this->createProfilePost(Content::VISIBILITY_PUBLIC, 'Third');

        $this->pinPost($post2);
        $this->pinPost($post1);

        $result = $this->createQuery()->all();

        $this->assertCount(3, $result);
        $this->assertEquals($post2->content->id, $result[0]->id);
        $this->assertEquals($post1->content->id, $result[1]->id);
        $this->assertEquals($post3->content->id, $result[2]->id);
    }

    /**
     * Make sure contributed posts are not actually pinned to the profile stream, but visible
     * @throws Exception
     */
    public function testPinnedSpacePostIsNotPinnedToProfileStream()
    {
        $post1 = $this->createProfilePost(Content::VISIBILITY_PUBLIC, 'First');
        $post2 = $this->createProfilePost(Content::VISIBILITY_PUBLIC, 'Second');
        $spacePost = $this->createPost(Space::findOne(2), Content::VISIBILITY_PUBLIC, 'Space');
        $post3 = $this->createProfilePost(Content::VISIBILITY_PUBLIC, 'Third');

        // We pin a profile and a contributed post
        $this->pinPost($post2);
        $this->pinPost($spacePost);

        $result = $this->createQuery()->all();

        $this->assertCount(4, $result);
        $this->assertEquals($post2->content->id, $result[0]->id); // Pinned post first
        $this->assertEquals($post3->content->id, $result[1]->id);
        $this->assertEquals($spacePost->content->id, $result[2]->id);
        $this->assertEquals($post1->content->id, $result[3]->id);
    }

    /**
     * Make sure non initial queries contain pinned contribution posts
     */
    public function testPinnedContributionPostIsLoadedInNonInitialQuery()
    {
        $spacePost = $this->createPost(Space::findOne(2), Content::VISIBILITY_PUBLIC, 'Space');
        $post1 = $this->createProfilePost(Content::VISIBILITY_PUBLIC, 'First');
        $post2 = $this->createProfilePost(Content::VISIBILITY_PUBLIC, 'Second');
        $post3 = $this->createProfilePost(Content::VISIBILITY_PUBLIC, 'Third');

        $this->pinPost($spacePost);

        $result = $this->createQuery()->from($post2->content->id)->all();
        $this->assertCount(2, $result);
        $this->assertEquals($post1->content->id, $result[0]->id);
        $this->assertEquals($spacePost->content->id, $result[1]->id);
    }

    /**
     * Make sure when loading a stream with content, the last entry in the result is not a pinned content in case there are
     * unpinned entries available
     */
    public function testLastStreamEntryOfInitialRequestNotPinned()
    {
        $post1 = $this->createProfilePost(Content::VISIBILITY_PUBLIC, 'First');
        $post2 = $this->createProfilePost(Content::VISIBILITY_PUBLIC, 'Second');
        $post3 = $this->createProfilePost(Content::VISIBILITY_PUBLIC, 'Third');
        $post4 = $this->createProfilePost(Content::VISIBILITY_PUBLIC, 'Forth');
        $post5 = $this->createProfilePost(Content::VISIBILITY_PUBLIC, 'Fifth');

        $this->pinPost($post1);
        $this->pinPost($post2);

        $result = $this->createQuery()->limit(2)->all();

        $this->assertCount(4, $result);
        $this->assertEquals($post2->content->id, $result[0]->id);
        $this->assertEquals($post1->content->id, $result[1]->id);
        $this->assertEquals($post5->content->id, $result[2]->id);
        $this->assertEquals($post4->content->id, $result[3]->id);

        // Second query
        $result2 = $this->createQuery()->from($post4->content->id)->all();
        $this->assertCount(1, $result2);
        $this->assertEquals($post3->content->id, $result2[0]->id);

    }

    protected function pinPost(Post $post)
    {
        $this->assertEquals(1, $post->content->updateAttributes(['pinned' => 1]));
    }

}
