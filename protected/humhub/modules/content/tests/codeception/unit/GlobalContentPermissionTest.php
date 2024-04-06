<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

namespace tests\codeception\unit\modules\content;

use Codeception\Specify;
use humhub\modules\content\models\Content;
use humhub\modules\post\models\Post;
use humhub\modules\user\models\User;
use humhub\modules\user\Module;
use tests\codeception\_support\HumHubDbTestCase;
use Throwable;
use Yii;
use yii\base\Exception;
use yii\base\InvalidConfigException;
use yii\db\IntegrityException;

class GlobalContentPermissionTest extends HumHubDbTestCase
{
    /**
     *  - User is the owner of the content
     *  - User is system administrator and the content module setting `adminCanEditAllContent` is set to true (default)
     *  - The user is granted the managePermission set by the model record class
     *  - The user meets the additional condition implemented by the model records class own `canEdit()` function.
     */
    use Specify;

    public $privatePost;
    public $publicPost;

    public function setUp(): void
    {
        parent::setUp();
        self::becomeUser('Admin');

        $this->privatePost = new Post();
        $this->privatePost->message = "Private Post";
        $this->privatePost->content->visibility = Content::VISIBILITY_PRIVATE;
        $this->privatePost->save();

        $this->publicPost = new Post();
        $this->publicPost->message = "Public Post";
        $this->publicPost->content->visibility = Content::VISIBILITY_PUBLIC;
        $this->publicPost->save();
    }

    /**
     * @throws InvalidConfigException
     * @throws IntegrityException
     * @throws Throwable
     * @throws Exception
     */
    public function testOwnerPermissions(): void
    {
        /** @var Module $userModule */
        $userModule = Yii::$app->getModule('user');

        // Test with guest access enabled
        $userModule->settings->set('auth.allowGuestAccess', true);
        $this->assertTrue($this->privatePost->content->canView());
        $this->assertTrue($this->privatePost->content->canEdit());
        $this->assertTrue($this->privatePost->content->canView());
        $this->assertTrue($this->publicPost->content->canEdit());

        // Test with guest access disabled should not have any effect
        $userModule->settings->set('auth.allowGuestAccess', false);
        $this->reloadPosts();
        $this->assertTrue($this->privatePost->content->canView());
        $this->assertTrue($this->privatePost->content->canEdit());
        $this->assertTrue($this->privatePost->content->canView());
        $this->assertTrue($this->publicPost->content->canEdit());
    }

    /**
     * @throws InvalidConfigException
     * @throws Throwable
     * @throws Exception
     * @throws IntegrityException
     */
    public function testUserPermission(): void
    {
        /** @var Module $userModule */
        $userModule = Yii::$app->getModule('user');

        $user3 = User::findOne(['id' => 4]);

        // Test with guest access enabled
        $userModule->settings->set('auth.allowGuestAccess', true);
        $this->assertTrue($this->privatePost->content->canView($user3));
        $this->assertTrue($this->publicPost->content->canView($user3));
        $this->assertFalse($this->publicPost->content->canEdit($user3));
        $this->assertFalse($this->privatePost->content->canEdit($user3));

        // Test with guest access disabled should not have any effect
        $userModule->settings->set('auth.allowGuestAccess', false);
        $this->assertTrue($this->privatePost->content->canView($user3));
        $this->assertTrue($this->publicPost->content->canView($user3));
        $this->assertFalse($this->publicPost->content->canEdit($user3));
        $this->assertFalse($this->privatePost->content->canEdit($user3));


        // Test again with logged-in user

        // Test with guest access enabled
        $userModule->settings->set('auth.allowGuestAccess', true);
        self::becomeUser('User3');
        $this->reloadPosts();
        $this->assertTrue($this->privatePost->content->canView());
        $this->assertTrue($this->publicPost->content->canView());
        $this->assertFalse($this->publicPost->content->canEdit());
        $this->assertFalse($this->privatePost->content->canEdit());

        // Test with guest access disabled should not have any effect
        $userModule->settings->set('auth.allowGuestAccess', false);
        $this->reloadPosts();
        $this->assertTrue($this->privatePost->content->canView());
        $this->assertTrue($this->publicPost->content->canView());
        $this->assertFalse($this->publicPost->content->canEdit());
        $this->assertFalse($this->privatePost->content->canEdit());
    }

    /**
     * @throws InvalidConfigException
     * @throws Throwable
     * @throws Exception
     * @throws IntegrityException
     */
    public function testGuestPermission(): void
    {
        /** @var Module $userModule */
        $userModule = Yii::$app->getModule('user');

        // Guest
        self::logout();

        // Enable guest access
        $userModule->settings->set('auth.allowGuestAccess', true);
        $this->reloadPosts();
        $this->assertFalse($this->privatePost->content->canView());
        $this->assertTrue($this->publicPost->content->canView());
        $this->assertFalse($this->publicPost->content->canEdit());
        $this->assertFalse($this->privatePost->content->canEdit());

        // Disable guest access
        $userModule->settings->set('auth.allowGuestAccess', false);
        $this->reloadPosts();
        $this->assertFalse($this->privatePost->content->canView());
        $this->assertFalse($this->publicPost->content->canView());
        $this->assertFalse($this->publicPost->content->canEdit());
        $this->assertFalse($this->privatePost->content->canEdit());
    }

    /**
     * Used for resetting the permissionmanager cache etc.
     */
    protected function reloadPosts(): void
    {
        $this->privatePost = Post::findOne(['id' => $this->privatePost->id]);
        $this->publicPost = Post::findOne(['id' => $this->publicPost->id]);
    }
}
