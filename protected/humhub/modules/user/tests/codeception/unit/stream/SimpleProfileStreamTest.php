<?php

namespace humhub\modules\user\tests\codeception\unit\stream;

use humhub\modules\comment\models\Comment;
use humhub\modules\content\models\Content;
use humhub\modules\content\widgets\richtext\ProsemirrorRichText;
use humhub\modules\content\widgets\richtext\RichText;
use humhub\modules\post\models\Post;
use humhub\modules\space\models\Space;
use humhub\modules\stream\models\StreamQuery;
use humhub\modules\user\models\Mentioning;
use humhub\modules\user\models\User;
use humhub\modules\user\notifications\Mentioned;
use humhub\modules\user\stream\filters\IncludeAllContributionsFilter;
use humhub\modules\user\stream\ProfileStreamQuery;
use humhub\modules\user\tests\codeception\_support\ProfileStreamTest;
use tests\codeception\_support\HumHubDbTestCase;
use yii\base\Exception;

class SimpleProfileStreamTest extends ProfileStreamTest
{
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
