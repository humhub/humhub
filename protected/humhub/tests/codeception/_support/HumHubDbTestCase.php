<?php

namespace tests\codeception\_support;

use humhub\libs\BasePermission;
use humhub\modules\content\components\ContentContainerPermissionManager;
use humhub\modules\content\tests\codeception\fixtures\ContentContainerFixture;
use humhub\modules\friendship\tests\codeception\fixtures\FriendshipFixture;
use humhub\modules\user\components\PermissionManager;
use humhub\modules\user\models\GroupPermission;
use humhub\modules\user\tests\codeception\fixtures\InviteFixture;
use humhub\modules\user\tests\codeception\fixtures\UserFixture;
use humhub\modules\user\tests\codeception\fixtures\UserFollowFixture;
use humhub\modules\user\tests\codeception\fixtures\UserFullFixture;
use humhub\modules\user\tests\codeception\fixtures\UserPasswordFixture;
use humhub\modules\user\tests\codeception\fixtures\UserProfileFixture;
use tests\codeception\unit\modules\content\ContentContainerStreamTest;
use Yii;
use yii\base\Event;
use yii\base\InvalidConfigException;
use yii\db\ActiveRecord;
use Codeception\TestCase\Test;
use humhub\modules\user\models\User;
use humhub\modules\notification\models\Notification;
use humhub\modules\activity\models\Activity;
use yii\di\Container;
use yii\test\FixtureTrait;

/**
 * Inherited Methods
 * @method void wantToTest($text)
 * @method void wantTo($text)
 * @method void execute($callable)
 * @method void expectTo($prediction)
 * @method void expect($prediction)
 * @method void amGoingTo($argumentation)
 * @method void am($role)
 * @method void lookForwardTo($achieveValue)
 * @method void comment($description)
 * @method \Codeception\Lib\Friend haveFriend($name, $actorClass = NULL)
 *
 * @SuppressWarnings(PHPMD)
 */
class HumHubDbTestCase extends Test
{

    protected $fixtureConfig;

    public $appConfig = '@tests/codeception/config/unit.php';

    public $time;

    protected function setUp()
    {
        parent::setUp();
        $webRoot = dirname(dirname(__DIR__)).'/../../..';
        Yii::setAlias('@webroot', realpath($webRoot));
        $this->initModules();
        $this->reloadSettings();
        $this->deleteMails();
    }

    protected function tearDown()
    {
        Event::offAll();
        parent::tearDown();
    }

    protected function reloadSettings()
    {
        Yii::$app->settings->reload();
        
        foreach (Yii::$app->modules as $module) {
            if ($module instanceof \humhub\components\Module) {
                $module->settings->reload();
            }
        }
    }

    protected function deleteMails()
    {
        $path = Yii::getAlias('@runtime/mail');
        $files = glob($path . '/*'); // get all file names
        foreach ($files as $file) { // iterate files
            if (is_file($file)) {
                unlink($file); // delete file
            }
        }
    }

    /**
     * Initializes modules defined in @tests/codeception/config/test.config.php
     * Note the config key in test.config.php is modules and not humhubModules!
     */
    protected function initModules()
    {
        $cfg = \Codeception\Configuration::config();
        if (!empty($cfg['humhub_modules'])) {
            Yii::$app->moduleManager->enableModules($cfg['humhub_modules']);
        }
    }

    /**
     * @inheritdoc
     */
    public function _fixtures()
    {
        $cfg = \Codeception\Configuration::config();

        if(!$this->fixtureConfig && isset($cfg['fixtures'])) {
            $this->fixtureConfig = $cfg['fixtures'];
        }

        $result = [];

        if (!empty($this->fixtureConfig)) {
            foreach ($this->fixtureConfig as $fixtureTable => $fixtureClass) {
                if ($fixtureClass === 'default') {
                    $result = array_merge($result, $this->getDefaultFixtures());
                } else {
                    $result[$fixtureTable] = ['class' => $fixtureClass];
                }
            }
        }

        return $result;
    }

    protected function getDefaultFixtures()
    {
        return [
            'user' => ['class' => UserFullFixture::class],
            'group_permission' => ['class' => \humhub\modules\user\tests\codeception\fixtures\GroupPermissionFixture::className()],
            'contentcontainer' => ['class' => ContentContainerFixture::class],
            'settings' => ['class' => \humhub\tests\codeception\fixtures\SettingFixture::className()],
            'space' => ['class' => \humhub\modules\space\tests\codeception\fixtures\SpaceFixture::className()],
            'space_membership' => ['class' => \humhub\modules\space\tests\codeception\fixtures\SpaceMembershipFixture::className()],
            'space_module' => ['class' => \humhub\modules\space\tests\codeception\fixtures\SpaceModuleFixture::className()],
            'content' => ['class' => \humhub\modules\content\tests\codeception\fixtures\ContentFixture::className()],
            'notification' => ['class' => \humhub\modules\notification\tests\codeception\fixtures\NotificationFixture::className()],
            'activity' => ['class' => \humhub\modules\activity\tests\codeception\fixtures\ActivityFixture::className()],
            'friendship' => ['class' => FriendshipFixture::class]
        ];
    }

    public function assertHasNotification($class, ActiveRecord $source, $originator_id = null, $msg = null)
    {
        $notificationQuery = Notification::find(['class' => $class, 'source_class' => $source->className(), 'source_pk' => $source->getPrimaryKey()]);

        if ($originator_id != null) {
            $notificationQuery->andWhere(['originator_user_id' => $originator_id]);
        }

        $this->assertNotEmpty($notificationQuery->all(), $msg);
    }

    public function assertHasActivity($class, ActiveRecord $source, $msg = null)
    {
        $activity = Activity::findOne(['class' => $class, 'object_model' => $source->className(), 'object_id' => $source->getPrimaryKey()]);
        $this->assertNotNull($activity, $msg);
    }

    public function assertMailSent($count = 0, $msg = null)
    {
        return $this->getModule('Yii2')->seeEmailIsSent($count);
    }

    public function assertEqualsLastEmailSubject($subject)
    {
        $message = $this->getModule('Yii2')->grabLastSentEmail();
        $this->assertEquals($subject, $message->getSubject());
    }

    public function allowGuestAccess($allow = true)
    {
        if($allow) {
            Yii::$app->getModule('user')->settings->set('auth.allowGuestAccess', 1);
        } else {
            Yii::$app->getModule('user')->settings->set('auth.allowGuestAccess', 0);
        }
    }

    public function setGroupPermission($groupId, $permission, $state = BasePermission::STATE_ALLOW)
    {
        $permissionManger = new PermissionManager();
        $permissionManger->setGroupState($groupId, $permission, $state);
        Yii::$app->user->permissionManager->clear();
    }

    public function setContentContainerPermission($contentContainer, $groupId, $permission, $state = BasePermission::STATE_ALLOW)
    {
        $permissionManger = new ContentContainerPermissionManager(['contentContainer' => $contentContainer]);
        $permissionManger->setGroupState($groupId, $permission, $state);
        $contentContainer->permissionManager->clear();
    }

    public function becomeUser($userName)
    {
        $user = User::findOne(['username' => $userName]);
        Yii::$app->user->switchIdentity($user);
    }

    public function logout()
    {
        Yii::$app->user->logout(true);
    }

}
