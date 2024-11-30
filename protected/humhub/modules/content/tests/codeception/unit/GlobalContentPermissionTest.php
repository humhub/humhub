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
use humhub\modules\content\tests\codeception\unit\TestContent;
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

    public $privateTestContent;
    public $publicTestContent;

    public function setUp(): void
    {
        parent::setUp();
        self::becomeUser('Admin');

        $this->privateTestContent = new TestContent();
        $this->privateTestContent->message = "Private TestContent";
        $this->privateTestContent->content->visibility = Content::VISIBILITY_PRIVATE;
        $this->privateTestContent->save();

        $this->publicTestContent = new TestContent();
        $this->publicTestContent->message = "Public TestContent";
        $this->publicTestContent->content->visibility = Content::VISIBILITY_PUBLIC;
        $this->publicTestContent->save();
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
        $this->assertTrue($this->privateTestContent->content->canView());
        $this->assertTrue($this->privateTestContent->content->canEdit());
        $this->assertTrue($this->privateTestContent->content->canView());
        $this->assertTrue($this->publicTestContent->content->canEdit());

        // Test with guest access disabled should not have any effect
        $userModule->settings->set('auth.allowGuestAccess', false);
        $this->reloadTestContents();
        $this->assertTrue($this->privateTestContent->content->canView());
        $this->assertTrue($this->privateTestContent->content->canEdit());
        $this->assertTrue($this->privateTestContent->content->canView());
        $this->assertTrue($this->publicTestContent->content->canEdit());
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
        $this->assertTrue($this->privateTestContent->content->canView($user3));
        $this->assertTrue($this->publicTestContent->content->canView($user3));
        $this->assertFalse($this->publicTestContent->content->canEdit($user3));
        $this->assertFalse($this->privateTestContent->content->canEdit($user3));

        // Test with guest access disabled should not have any effect
        $userModule->settings->set('auth.allowGuestAccess', false);
        $this->assertTrue($this->privateTestContent->content->canView($user3));
        $this->assertTrue($this->publicTestContent->content->canView($user3));
        $this->assertFalse($this->publicTestContent->content->canEdit($user3));
        $this->assertFalse($this->privateTestContent->content->canEdit($user3));


        // Test again with logged-in user

        // Test with guest access enabled
        $userModule->settings->set('auth.allowGuestAccess', true);
        self::becomeUser('User3');
        $this->reloadTestContents();
        $this->assertTrue($this->privateTestContent->content->canView());
        $this->assertTrue($this->publicTestContent->content->canView());
        $this->assertFalse($this->publicTestContent->content->canEdit());
        $this->assertFalse($this->privateTestContent->content->canEdit());

        // Test with guest access disabled should not have any effect
        $userModule->settings->set('auth.allowGuestAccess', false);
        $this->reloadTestContents();
        $this->assertTrue($this->privateTestContent->content->canView());
        $this->assertTrue($this->publicTestContent->content->canView());
        $this->assertFalse($this->publicTestContent->content->canEdit());
        $this->assertFalse($this->privateTestContent->content->canEdit());
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
        $this->reloadTestContents();
        $this->assertFalse($this->privateTestContent->content->canView());
        $this->assertTrue($this->publicTestContent->content->canView());
        $this->assertFalse($this->publicTestContent->content->canEdit());
        $this->assertFalse($this->privateTestContent->content->canEdit());

        // Disable guest access
        $userModule->settings->set('auth.allowGuestAccess', false);
        $this->reloadTestContents();
        $this->assertFalse($this->privateTestContent->content->canView());
        $this->assertFalse($this->publicTestContent->content->canView());
        $this->assertFalse($this->publicTestContent->content->canEdit());
        $this->assertFalse($this->privateTestContent->content->canEdit());
    }

    /**
     * Used for resetting the permissionmanager cache etc.
     */
    protected function reloadTestContents(): void
    {
        $this->privateTestContent = TestContent::findOne(['id' => $this->privateTestContent->id]);
        $this->publicTestContent = TestContent::findOne(['id' => $this->publicTestContent->id]);
    }
}
