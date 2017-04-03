<?php

namespace humhub\modules\notification\tests\codeception\unit\category;

use Yii;
use tests\codeception\_support\HumHubDbTestCase;
use Codeception\Specify;
use humhub\modules\notification\tests\codeception\unit\category\notifications\TestNotification;
use humhub\modules\notification\tests\codeception\unit\category\notifications\SpecialNotification;
use humhub\modules\notification\models\forms\NotificationSettings;
use humhub\modules\notification\targets\MailTarget;
use humhub\modules\notification\targets\WebTarget;

class NotificationCategoryTest extends HumHubDbTestCase
{

    use Specify;

    /**
     * Test the setting of a global category setting.
     */
    public function testGlobalCategorySetting()
    {
        $this->becomeUser('Admin');

        $notification = new TestNotification();
        $category = $notification->getCategory();
        $mailTarget = Yii::$app->notification->getTarget(MailTarget::class);
        $webTarget = Yii::$app->notification->getTarget(WebTarget::class);

        $this->assertFalse($mailTarget->isEnabled($notification));
        $this->assertTrue($webTarget->isEnabled($notification));

        $settingForm = new NotificationSettings([
            'settings' => [
                $mailTarget->getSettingKey($category) => true,
                $webTarget->getSettingKey($category) => false,
            ]
        ]);

        $settingForm->save();

        $this->assertTrue($mailTarget->isEnabled($notification));
        $this->assertFalse($webTarget->isEnabled($notification));
    }

    /**
     * Test fixed category settings.
     */
    public function testFixedCategorySetting()
    {
        $this->becomeUser('Admin');

        $notification = new SpecialNotification();
        $category = $notification->getCategory();
        $mailTarget = Yii::$app->notification->getTarget(MailTarget::class);
        $webTarget = Yii::$app->notification->getTarget(WebTarget::class);

        $this->assertFalse($mailTarget->isEnabled($notification));
        $this->assertFalse($webTarget->isEnabled($notification));

        // Set true for both
        $settingForm = new NotificationSettings([
            'settings' => [
                $mailTarget->getSettingKey($category) => true,
                $webTarget->getSettingKey($category) => true,
            ]
        ]);

        $settingForm->save();

        // Check that setting does not effect fixed target setting.
        $this->assertTrue($webTarget->isEnabled($notification));
        $this->assertFalse($mailTarget->isEnabled($notification));
    }

    public function testInvisibleCategorySetting()
    {
        // SpecialCategory is invisible for this user.
        $this->becomeUser('User1');
        $user = Yii::$app->user->getIdentity();

        $this->becomeUser('Admin');
        $notification = new SpecialNotification();
        $category = $notification->getCategory();
        $mailTarget = Yii::$app->notification->getTarget(MailTarget::class);
        $webTarget = Yii::$app->notification->getTarget(WebTarget::class);

        $this->assertFalse($mailTarget->isEnabled($notification));
        $this->assertFalse($webTarget->isEnabled($notification));

        // Set global settings to true for both targets
        $settingForm = new NotificationSettings([
            'settings' => [
                $mailTarget->getSettingKey($category) => true,
                $webTarget->getSettingKey($category) => true,
            ]
        ]);

        $settingForm->save();

        // Check this does not affect the decision for this user
        $this->assertFalse($webTarget->isEnabled($notification, $user));
        $this->assertFalse($mailTarget->isEnabled($notification, $user));

        // Save again for the user
        $settingForm = new NotificationSettings([
            'settings' => [
                $mailTarget->getSettingKey($category) => true,
                $webTarget->getSettingKey($category) => true,
            ]
        ]);

        $settingForm->save($user);

        // Check this does not affect the decision for this user
        $this->assertFalse($webTarget->isEnabled($notification, $user));
        $this->assertFalse($mailTarget->isEnabled($notification, $user));
    }

    public function testUserCategorySetting()
    {
        $this->becomeUser('User2');
        $user = Yii::$app->user->getIdentity();

        $notification = new TestNotification();
        $category = $notification->getCategory();
        $mailTarget = Yii::$app->notification->getTarget(MailTarget::class);
        $webTarget = Yii::$app->notification->getTarget(WebTarget::class);

        // Check default settings.
        $this->assertFalse($mailTarget->isEnabled($notification, $user));
        $this->assertTrue($webTarget->isEnabled($notification, $user));

        $this->becomeUser('Admin');

        // Change global default settings, deny both targets.
        $settingForm = new NotificationSettings([
            'settings' => [
                $mailTarget->getSettingKey($category) => false,
                $webTarget->getSettingKey($category) => false,
            ]
        ]);

        $settingForm->save();

        // Check if global defaults effected user check
        $this->assertFalse($mailTarget->isEnabled($notification, $user));
        $this->assertFalse($webTarget->isEnabled($notification, $user));

        $this->becomeUser('User2');

        // Change user settings.
        $userSettings = new NotificationSettings([
            'user' => $user,
            'settings' => [
                $mailTarget->getSettingKey($category) => true,
                $webTarget->getSettingKey($category) => true,
            ]
        ]);

        $userSettings->save();

        // Check that global settings are unaffected
        $this->assertFalse($mailTarget->isEnabled($notification));
        $this->assertFalse($webTarget->isEnabled($notification));

        // Check if user settings
        $this->assertTrue($mailTarget->isEnabled($notification, $user));
        $this->assertTrue($webTarget->isEnabled($notification, $user));
    }

}
