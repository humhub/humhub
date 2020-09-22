<?php

namespace tests\codeception\unit\modules\content;

use humhub\modules\user\models\User;
use Yii;
use tests\codeception\_support\HumHubDbTestCase;
use humhub\modules\post\models\Post;

use humhub\modules\space\models\Space;
use humhub\modules\content\models\Content;

class ReadableContentQueryTest extends HumHubDbTestCase
{

    /**
     * @var User
     */
    private $user;
    private $publicSpace;
    private $privateSpace;
    private $globalPublicPost;
    private $globalPrivatePost;
    private $publicSpacePublicPost;
    private $publicSpacePrivatePost;
    private $privateSpacePublicPost;
    private $privateSpacePrivatePost;
    private $profilePublicPost;
    private $profilePrivatePost;
    private $posts = [];

    public function _before()
    {
        parent::_before();

        // TODO: would be cleaner to somehow exclude this from default fixtures
        foreach (Post::find()->all() as $post) {
            $post->delete();
        }

        $this->posts = [];

        // Note: User2 is moderator of Space3
        // Note: User1 is member of Space3
        // Note: User3 is not member of Space3
        $this->becomeUser('User2');
        $this->publicSpace = Space::findOne(['id' => 3]);
        $this->privateSpace = Space::findOne(['id' => 5]);
        $this->user = Yii::$app->user->identity;

        $this->globalPrivatePost = new Post;
        $this->globalPrivatePost->message = "Global Public Post";
        $this->globalPrivatePost->content->visibility = Content::VISIBILITY_PRIVATE;
        $this->globalPrivatePost->save();

        $this->globalPublicPost = new Post;
        $this->globalPublicPost->message = "Global Private Post";
        $this->globalPublicPost->content->visibility = Content::VISIBILITY_PUBLIC;
        $this->globalPublicPost->save();

        $this->publicSpacePublicPost = new Post;
        $this->publicSpacePublicPost->message = "Public Space Public Post";
        $this->publicSpacePublicPost->content->visibility = Content::VISIBILITY_PUBLIC;
        $this->publicSpacePublicPost->content->setContainer($this->publicSpace);
        $this->publicSpacePublicPost->save();

        $this->publicSpacePrivatePost = new Post;
        $this->publicSpacePrivatePost->message = "Public Space Private Post";
        $this->publicSpacePrivatePost->content->visibility = Content::VISIBILITY_PRIVATE;
        $this->publicSpacePrivatePost->content->setContainer($this->publicSpace);
        $this->publicSpacePrivatePost->save();

        $this->profilePublicPost = new Post;
        $this->profilePublicPost->message = "Profile Public Post";
        $this->profilePublicPost->content->visibility = Content::VISIBILITY_PUBLIC;
        $this->profilePublicPost->content->setContainer($this->user);
        $this->profilePublicPost->save();

        $this->profilePrivatePost = new Post;
        $this->profilePrivatePost->message = "Profile Private Post";
        $this->profilePrivatePost->content->visibility = Content::VISIBILITY_PRIVATE;
        $this->profilePrivatePost->content->setContainer($this->user);
        $this->profilePrivatePost->save();

        // User1 is member of the private space
        $this->becomeUser('User1');

        // Note: public content in a private space should not exist
        $this->privateSpacePublicPost = new Post;
        $this->privateSpacePublicPost->message = "Private Space Public Post";
        $this->privateSpacePublicPost->content->visibility = Content::VISIBILITY_PUBLIC;
        $this->privateSpacePublicPost->content->setContainer($this->privateSpace);
        $this->privateSpacePublicPost->save();

        $this->privateSpacePrivatePost = new Post;
        $this->privateSpacePrivatePost->message = "Private Space Private Post";
        $this->privateSpacePrivatePost->content->visibility = Content::VISIBILITY_PRIVATE;
        $this->privateSpacePrivatePost->content->setContainer($this->privateSpace);
        $this->privateSpacePrivatePost->save();
    }

    /**
     * This test queries all visible global posts for User1 (not the content owner)
     * The should be able to see both global content, since he is member of the network.
     *
     * @throws \Throwable
     * @throws \yii\base\Exception
     */
    public function testGlobalContentAsMember()
    {
        $this->becomeUser('User1');
        $posts = Post::find()->contentContainer(null)->readable()->all();
        $this->assertCount(2, $posts);
        $this->assertEquals($this->globalPublicPost->id, $posts[1]->id);
        $this->assertEquals($this->globalPrivatePost->id, $posts[0]->id);
    }

    public function testGlobalContentAsGuest()
    {
        $this->allowGuestAccess();
        $this->logout();

        $this->posts = Post::find()->contentContainer(null)->readable()->all();

        $this->assertPostCount(1);
        $this->assertInPosts($this->globalPublicPost);
    }

    public function testGlobalContentGuestNonGuestMode()
    {
        $this->allowGuestAccess(false);
        $this->logout();

        $posts = Post::find()->contentContainer(null)->readable()->all();

        $this->assertPostCount(0);
    }

    public function testPublicSpaceContentAsMember()
    {
        $this->becomeUser('User1');

        $this->posts = Post::find()->contentContainer($this->publicSpace)->readable()->all();

        $this->assertPostCount(2);
        $this->assertInPosts($this->publicSpacePublicPost);
        $this->assertInPosts($this->publicSpacePrivatePost);
    }

    public function testPublicSpaceContentAsNonMember()
    {
        $this->becomeUser('User3');

        $this->posts  = Post::find()->contentContainer($this->publicSpace)->readable()->all();

        $this->assertPostCount(1);
        $this->assertInPosts($this->publicSpacePublicPost);
    }

    public function testPublicSpaceContentAsGuest()
    {
        $this->allowGuestAccess(true);
        $this->logout();

        $this->posts  = Post::find()->contentContainer($this->publicSpace)->readable()->all();

        $this->assertPostCount(1);
        $this->assertInPosts($this->publicSpacePublicPost);
    }

    public function testPublicSpaceContentAsGuestNonGuestMode()
    {
        $this->allowGuestAccess(false);
        $this->logout();

        $posts = Post::find()->contentContainer($this->publicSpace)->readable()->all();

        $this->assertCount(0, $posts);
    }

    public function testPrivateSpaceContentAsMember()
    {
        $this->becomeUser('User1');

        $this->posts = Post::find()->contentContainer($this->privateSpace)->readable()->all();

        $this->assertPostCount(2);
        $this->assertInPosts($this->privateSpacePublicPost);
        $this->assertInPosts($this->privateSpacePrivatePost);
    }


    public function tesPrivateSpaceContentAsNonMember()
    {
        $this->becomeUser('User3');

        $this->posts = Post::find()->contentContainer($this->privateSpace)->readable()->all();

        $this->assertPostCount(0);
    }


    public function testPrivateSpaceContentAsGuest()
    {
        $this->logout();

        $this->posts = Post::find()->contentContainer($this->privateSpace)->readable()->all();

        $this->assertPostCount(0);
    }

    public function testProfileContentOfGlobalUserAsOwner()
    {
        $this->becomeUser('User2');
        $this->user->updateAttributes(['visibility' => User::VISIBILITY_ALL]);

        $this->posts = Post::find()->contentContainer($this->user)->readable()->all();

        $this->assertPostCount(2);
        $this->assertInPosts($this->profilePublicPost);
        $this->assertInPosts($this->profilePrivatePost);
    }

    public function testProfileContentOfMembersOnlyUserAsOwner()
    {
        $this->becomeUser('User2');
        $this->user->updateAttributes(['visibility' => User::VISIBILITY_REGISTERED_ONLY]);

        $this->posts = Post::find()->contentContainer($this->user)->readable()->all();

        $this->assertPostCount(2);
        $this->assertInPosts($this->profilePublicPost);
        $this->assertInPosts($this->profilePrivatePost);
    }

    public function testProfileContentOfGlobalUserAsMember()
    {
        $this->becomeUser('User1');
        $this->user->updateAttributes(['visibility' => User::VISIBILITY_ALL]);

        $this->posts = Post::find()->contentContainer($this->user)->readable()->all();

        $this->assertPostCount(1);
        $this->assertInPosts($this->profilePublicPost);
    }

    public function testProfileContentOfMembersOnlyUser()
    {
        $this->becomeUser('User1');
        $this->user->updateAttributes(['visibility' => User::VISIBILITY_REGISTERED_ONLY]);

        $this->posts = Post::find()->contentContainer($this->user)->readable()->all();

        $this->assertPostCount(1);
        $this->assertInPosts($this->profilePublicPost);
    }

    public function testProfileContentOfGlobalUserAsGuest()
    {
        $this->allowGuestAccess();
        $this->user->updateAttributes(['visibility' => User::VISIBILITY_ALL]);

        $this->logout();

        $this->posts = Post::find()->contentContainer($this->user)->readable()->all();

        $this->assertPostCount(1);
        $this->assertInPosts($this->profilePublicPost);
    }

    public function testProfileContentOfGlobalUserAsGuestNonGuestMode()
    {
        $this->allowGuestAccess(false);
        $this->user->updateAttributes(['visibility' => User::VISIBILITY_ALL]);

        $this->logout();

        $this->posts = Post::find()->contentContainer($this->user)->readable()->all();

        $this->assertPostCount(0);
    }

    public function testProfileContentOfMembersOnlyUserAsGuest()
    {
        $this->allowGuestAccess();
        $this->user->updateAttributes(['visibility' => User::VISIBILITY_REGISTERED_ONLY]);

        $this->logout();

        $this->posts = Post::find()->contentContainer($this->user)->readable()->all();

        $this->assertPostCount(0);
    }

    public function testReadableOnlyAsMemberOfPrivateSpace()
    {
        $this->user->updateAttributes(['visibility' => User::VISIBILITY_ALL]);
        $this->becomeUser('User1');
        $this->posts = Post::find()->readable()->all();
        $this->assertPostCount(7);

        $this->assertInPosts($this->publicSpacePublicPost);
        $this->assertInPosts($this->publicSpacePrivatePost);
        $this->assertInPosts($this->privateSpacePublicPost);

        $this->assertInPosts($this->privateSpacePrivatePost);
        $this->assertInPosts($this->globalPrivatePost);
        $this->assertInPosts($this->globalPublicPost);
        $this->assertInPosts($this->profilePublicPost);
    }

    public function testReadableOnlyAsUserProfileOwner()
    {
        $this->user->updateAttributes(['visibility' => User::VISIBILITY_ALL]);
        $this->becomeUser('User2');
        $this->posts = Post::find()->readable()->all();
        $this->assertPostCount(6);

        $this->assertInPosts($this->publicSpacePublicPost);
        $this->assertInPosts($this->publicSpacePrivatePost);
        $this->assertInPosts($this->globalPrivatePost);
        $this->assertInPosts($this->globalPublicPost);
        $this->assertInPosts($this->profilePublicPost);
        $this->assertInPosts($this->profilePrivatePost);
    }

    public function testReadableOnlyAsUserProfileOwnerWithNonMemberSpacePost()
    {
        // Tests if the query includes posts a user created, but is not member of the related space anymore
        $this->user->updateAttributes(['visibility' => User::VISIBILITY_ALL]);
        $this->becomeUser('User2');


        // This could be an old post, and the user was removed from the space
        $privateSpacePost = new Post;
        $privateSpacePost->message = "Profile Public Post";
        $privateSpacePost->content->visibility = Content::VISIBILITY_PUBLIC;
        $privateSpacePost->content->setContainer($this->user);
        $privateSpacePost->save();

        $this->posts = Post::find()->readable()->all();
        $this->assertPostCount(7);

        $this->assertInPosts($this->publicSpacePublicPost);
        $this->assertInPosts($this->publicSpacePrivatePost);
        $this->assertInPosts($this->globalPrivatePost);
        $this->assertInPosts($this->globalPublicPost);
        $this->assertInPosts($this->profilePublicPost);
        $this->assertInPosts($this->profilePrivatePost);
        $this->assertInPosts($privateSpacePost);
    }

    public function testReadableOnlyAsGuest()
    {
        $this->allowGuestAccess();
        $this->user->updateAttributes(['visibility' => User::VISIBILITY_ALL]);

        $this->logout();
        $this->posts = Post::find()->readable()->all();
        $this->assertPostCount(3);

        $this->assertInPosts($this->publicSpacePublicPost);
        $this->assertInPosts($this->globalPublicPost);
        $this->assertInPosts($this->profilePublicPost);
    }

    private function assertPostCount($count)
    {
        $this->assertCount($count, $this->posts);
    }

    private function assertInPosts($post)
    {
        $found = false;
        foreach ($this->posts as $postResult) {
            if($postResult->id === $post->id) {
                $found = true;
                break;
            }
        }
        $this->assertTrue($found, "Could not find {$post->id} in result");
    }
}
