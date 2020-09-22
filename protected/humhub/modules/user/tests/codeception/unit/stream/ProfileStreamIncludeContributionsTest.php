<?php


namespace modules\user\tests\codeception\unit\stream;


use humhub\modules\content\models\Content;
use humhub\modules\space\models\Space;
use humhub\modules\user\tests\codeception\_support\ProfileStreamTest;
use yii\base\Exception;

class ProfileStreamIncludeContributionsTest extends ProfileStreamTest
{
    /**
     * Make sure profile owner sees public space contributions
     * @throws Exception
     */
    public function testProfileOwnerSeesPublicSpacePost()
    {
        $post = $this->createPost(Space::findOne(2), Content::VISIBILITY_PUBLIC);
        $result = $this->createQuery(true)->all();

        $this->assertCount(1, $result);
        $this->assertEquals($post->content->id, $result[0]->id);
    }

    /**
     * Make sure profile owner does not see public space contributions if contribution filter not active
     * @throws Exception
     */
    public function testProfileOwnerDoesNotSeePublicSpacePostWithoutContributionsFilter()
    {
        $this->createPost(Space::findOne(2), Content::VISIBILITY_PUBLIC);
        $this->assertEmpty( $this->createQuery(false)->all());
    }

    /**
     * Make sure profile owner sees private space contributions
     * @throws Exception
     */
    public function testProfileOwnerSeesPrivateSpacePost()
    {
        $post = $this->createPost(Space::findOne(2), Content::VISIBILITY_PRIVATE);
        $result = $this->createQuery(true)->all();

        $this->assertCount(1, $result);
        $this->assertEquals($post->content->id, $result[0]->id);
    }

    /**
     * Make sure profile owner does not see private space contributions if contribution filter not active
     * @throws Exception
     */
    public function testProfileOwnerDoesNotSeePrivateSpacePostWithoutContributionsFilter()
    {
        $this->createPost(Space::findOne(2), Content::VISIBILITY_PRIVATE);
        $this->assertEmpty( $this->createQuery(false)->all());
    }

    /**
     * Make sure user sees private contributions of member spaces in another profile stream
     *
     * @throws Exception
     */
    public function testUserDoesNotSeePrivateSpacePostAsNonMember()
    {
        $this->createPost(Space::findOne(2), Content::VISIBILITY_PRIVATE);

        // User2 is not member of space2
        $this->becomeUser('User2');

        $this->assertEmpty($this->createQuery(true)->all());
    }

    /**
     * Make sure user does not see private contributions of non member spaces in another profile stream
     *
     * @throws Exception
     */
    public function testUserSeesPrivateSpacePostAsMember()
    {
        $post = $this->createPost(Space::findOne(3), Content::VISIBILITY_PRIVATE);

        // User2 is not member of space3
        $this->becomeUser('User2');

        $result = $this->createQuery(true)->all();
        $this->assertCount(1, $result);
        $this->assertEquals($post->content->id, $result[0]->id);
    }

    /**
     * Make sure user sees public contributions of member spaces in another profile stream
     *
     * @throws Exception
     */
    public function testUserSeesPublicSpacePostAsNonMember()
    {
        $post = $this->createPost(Space::findOne(2), Content::VISIBILITY_PUBLIC);

        // User2 is not member of space2
        $this->becomeUser('User2');

        $result = $this->createQuery(true)->all();
        $this->assertCount(1, $result);
        $this->assertEquals($post->content->id, $result[0]->id);
    }

    /**
     * Make sure user sees public contributions of non member spaces in another profile stream
     *
     * @throws Exception
     */
    public function testUserSeesPublicSpacePostAsMember()
    {
        $post = $this->createPost(Space::findOne(3), Content::VISIBILITY_PUBLIC);

        // User2 is not member of space3
        $this->becomeUser('User2');

        $result = $this->createQuery(true)->all();
        $this->assertCount(1, $result);
        $this->assertEquals($post->content->id, $result[0]->id);
    }

}
