<?php

namespace humhub\modules\user\tests\codeception\unit\stream;

use humhub\modules\content\models\Content;
use humhub\modules\user\stream\ProfileStreamQuery;
use yii\base\Exception;

class SimpleProfileStreamNoContributionsTest extends SimpleProfileStreamTest
{
    /**
     * @return ProfileStreamQuery
     */
    protected function createQuery($includeContributions = false)
    {
        return new ProfileStreamQuery(['container' => $this->user, 'includeContributions' => $includeContributions]);
    }

    /**
     * @throws Exception
     */
    public function testEmptyStream()
    {
        $this->assertEmpty($this->createQuery()->all());
    }

    /**
     * Make sure profile owner sees public content
     * @throws Exception
     */
    public function testProfileOwnerPublicProfilePost()
    {
        $post = $this->createProfilePost(Content::VISIBILITY_PUBLIC);
        $result = $this->createQuery()->all();

        $this->assertCount(1, $result);
        $this->assertEquals($post->content->id, $result[0]->id);
    }

    /**
     * Make sure profile owner sees private content
     * @throws Exception
     */
    public function testProfileOwnerPrivateProfilePost()
    {
        $post = $this->createProfilePost(Content::VISIBILITY_PRIVATE);
        $result = $this->createQuery()->all();

        $this->assertCount(1, $result);
        $this->assertEquals($post->content->id, $result[0]->id);
    }

    /**
     * Make sure public content is visible for non friend users if friendship system is not active
     *
     * @throws Exception
     */
    public function testUserAccessPublicProfilePost()
    {
        $post = $this->createProfilePost(Content::VISIBILITY_PUBLIC);


        $this->becomeUser('User2');

        $result = $this->createQuery()->all();

        $this->assertCount(1, $result);
        $this->assertEquals($post->content->id, $result[0]->id);
    }

    /**
     * Make sure profile stream does not include private content for non friends if friendship system is deactivated
     * @throws Exception
     */
    public function testUserAccessPrivateProfilePost()
    {
        $this->createProfilePost(Content::VISIBILITY_PRIVATE);

        $this->becomeUser('User2');

        $this->assertEmpty($this->createQuery()->all());
    }

    /**
     * Make sure guests can see public profile posts
     * @throws Exception
     */
    public function testGuestAccessPublicProfilePost()
    {
        $post = $this->createProfilePost(Content::VISIBILITY_PUBLIC);

        $this->logout();

        $result = $this->createQuery()->all();

        $this->assertCount(1, $result);
        $this->assertEquals($post->content->id, $result[0]->id);
    }

    /**
     * Make sure profile stream does not include private content for non friends if friendship system is deactivated
     * @throws Exception
     */
    public function testGuestAccessPrivateProfilePost()
    {
        $this->createProfilePost(Content::VISIBILITY_PRIVATE);

        $this->logout();

        $this->assertEmpty($this->createQuery()->all());
    }
}
