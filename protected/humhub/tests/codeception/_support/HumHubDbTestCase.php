<?php

/*
 * @link      https://www.humhub.org/
 * @copyright Copyright (c) 2023 HumHub GmbH & Co. KG
 * @license   https://www.humhub.com/licences
 */

/**
 * @noinspection PhpIllegalPsrClassPathInspection
 */

namespace tests\codeception\_support;

use humhub\models\ClassMap;
use Codeception\Configuration;
use Codeception\Test\Unit;
use humhub\libs\BasePermission;
use humhub\modules\activity\tests\codeception\fixtures\ActivityFixture;
use humhub\modules\content\components\ContentContainerPermissionManager;
use humhub\modules\content\tests\codeception\fixtures\ContentContainerFixture;
use humhub\modules\content\tests\codeception\fixtures\ContentFixture;
use humhub\modules\file\tests\codeception\fixtures\FileFixture;
use humhub\modules\file\tests\codeception\fixtures\FileHistoryFixture;
use humhub\modules\friendship\models\Friendship;
use humhub\modules\friendship\tests\codeception\fixtures\FriendshipFixture;
use humhub\modules\live\tests\codeception\fixtures\LiveFixture;
use humhub\modules\notification\tests\codeception\fixtures\NotificationFixture;
use humhub\modules\space\tests\codeception\fixtures\SpaceFixture;
use humhub\modules\space\tests\codeception\fixtures\SpaceMembershipFixture;
use humhub\modules\user\components\PermissionManager;
use humhub\modules\user\models\User;
use humhub\modules\user\tests\codeception\fixtures\GroupPermissionFixture;
use humhub\modules\user\tests\codeception\fixtures\UserFullFixture;
use humhub\tests\codeception\fixtures\SettingFixture;
use humhub\tests\codeception\fixtures\UrlOembedFixture;
use PHPUnit\Framework\SkippedTestError;
use Yii;

/**
 * @SuppressWarnings(PHPMD)
 */
class HumHubDbTestCase extends Unit
{
    use HumHubHelperTrait;

    protected $fixtureConfig;

    public $appConfig = '@tests/codeception/config/unit.php';

    public $time;

    public array $firedEvents = [];

    protected function setUp(): void
    {
        if (\Yii::$app !== null) {
            \Yii::$app->db->trigger('afterOpen');
        }

        if (Yii::$app === null) {
            $c = new \ReflectionClass($this);
            $m = $c->getMethod($this->getName(false));
            $doc = $m->getDocComment();
            if (preg_match('#@skip(.*?)\r?\n#s', $doc, $annotations)) {
                throw new SkippedTestError("Test was skipped due to @skip annotation: " . (trim($annotations[1]) ?: "[No reason indicated!]"), 0);
            }
            return;
        }

        $webRoot = dirname(__DIR__, 2) . '/../../..';
        Yii::setAlias('@webroot', realpath($webRoot));
        static::initModules(__METHOD__);
        static::reloadSettings(__METHOD__);
        static::flushCache(__METHOD__);
        static::deleteMails(__METHOD__);

        parent::setUp();
    }

    /**
     * Initializes modules defined in @tests/codeception/config/test.config.php
     * Note the config key in test.config.php is modules and not humhubModules!
     */
    protected static function initModules(?string $caller = null)
    {
        codecept_debug(sprintf('[%s] Initializing Modules', $caller ?? __METHOD__));
        $cfg = Configuration::config();

        if (!empty($cfg['humhub_modules'])) {
            Yii::$app->moduleManager->enableModules($cfg['humhub_modules']);
        }
    }

    /* @codingStandardsIgnoreLine PSR2.Methods.MethodDeclaration.Underscore */
    public function _fixtures(): array
    {
        $cfg = Configuration::config();

        if (!$this->fixtureConfig && isset($cfg['fixtures'])) {
            $this->fixtureConfig = $cfg['fixtures'];
        }

        $result = [];

        if (!empty($this->fixtureConfig)) {
            foreach ($this->fixtureConfig as $fixtureTable => $fixtureClass) {
                if ($fixtureClass === 'default') {
                    $result = array_merge($result, static::getDefaultFixtures());
                } else {
                    $result[$fixtureTable] = ['class' => $fixtureClass];
                }
            }
        }

        return $result;
    }

    protected static function getDefaultFixtures(): array
    {
        return [
            'user' => ['class' => UserFullFixture::class],
            'url_oembed' => ['class' => UrlOembedFixture::class],
            'group_permission' => ['class' => GroupPermissionFixture::class],
            'contentcontainer' => ['class' => ContentContainerFixture::class],
            'settings' => ['class' => SettingFixture::class],
            'space' => ['class' => SpaceFixture::class],
            'space_membership' => ['class' => SpaceMembershipFixture::class],
            'content' => ['class' => ContentFixture::class],
            'notification' => ['class' => NotificationFixture::class],
            'file' => ['class' => FileFixture::class],
            'file_history' => ['class' => FileHistoryFixture::class],
            'activity' => ['class' => ActivityFixture::class],
            'friendship' => ['class' => FriendshipFixture::class],
            'live' => [ 'class' => LiveFixture::class]
        ];
    }

    public function assertHasNotification($class, ActiveRecord $source, $originator_id = null, $target_id = null, $msg = '')
    {
        $notificationQuery = Notification::find()->where([
            'class' => $class,
            'source_class_id' => ClassMap::getIdBy($source),
            'source_pk' => $source->getPrimaryKey(),
        ]);
        if(is_string($target_id)) {
            $msg = $target_id;
            $target_id = null;
        }

        if ($originator_id != null) {
            $notificationQuery->andWhere(['originator_user_id' => $originator_id]);
        }

        if($target_id != null) {
            $notificationQuery->andWhere(['user_id' => $target_id]);
        }

        $this->assertNotEmpty($notificationQuery->all(), $msg);
    }

    public function assertEqualsNotificationCount($count, $class, ActiveRecord $source, $originator_id = null, $target_id = null, $msg = '')
    {
        $notificationQuery = Notification::find()->where(['class' => $class, 'source_class_id' => ClassMap::getIdBy($source), 'source_pk' => $source->getPrimaryKey()]);

        if ($originator_id != null) {
            $notificationQuery->andWhere(['originator_user_id' => $originator_id]);
        }

        if($target_id != null) {
            $notificationQuery->andWhere(['user_id' => $target_id]);
        }

        $this->assertEquals($count, $notificationQuery->count(), $msg);
    }

    public function assertHasNoNotification($class, ActiveRecord $source, $originator_id = null, $target_id = null, $msg = '')
    {
        $notificationQuery = Notification::find()->where(['class' => $class, 'source_class_id' => ClassMap::getIdBy($source), 'source_pk' => $source->getPrimaryKey()]);

        if ($originator_id != null) {
            $notificationQuery->andWhere(['originator_user_id' => $originator_id]);
        }

        if($target_id != null) {
            $notificationQuery->andWhere(['user_id' => $target_id]);
        }

        $this->assertEmpty($notificationQuery->all(), $msg);
    }

    public function assertHasActivity($class, ActiveRecord $source, $msg = '')
    {
        $activity = Activity::findOne([
            'class' => $class,
            'object_model' => $source->className(),
            'object_id' => $source->getPrimaryKey(),
        ]);
        $this->assertNotNull($activity, $msg);
    }

    /**
     * @return \Codeception\Module\Yii2|\Codeception\Module
     * @throws \Codeception\Exception\ModuleException
     */
    public function getYiiModule() {
        return $this->getModule('Yii2');
    }

    /**
     * @see assertSentEmail
     * @since 1.3
     */
    public function assertMailSent($count = 0)
    {
        return $this->getYiiModule()->seeEmailIsSent($count);
    }

    /**
     * @param int $count
     * @throws \Codeception\Exception\ModuleException
     * @since 1.3
     */
    public function assertSentEmail($count = 0)
    {
        return $this->getYiiModule()->seeEmailIsSent($count);
    }

    public function assertEqualsLastEmailTo($to, $strict = true)
    {
        if(is_string($to)) {
            $to = [$to];
        }

        $message = $this->getYiiModule()->grabLastSentEmail();
        $expected = $message->getTo();

        foreach ($to as $email) {
            $this->assertArrayHasKey($email, $expected);
        }

        if($strict) {
            $this->assertEquals(count($to), count($expected));
        }

    }

    public function assertEqualsLastEmailSubject($subject)
    {
        $message = $this->getYiiModule()->grabLastSentEmail();
        $this->assertEquals($subject, str_replace(["\n", "\r"], '', $message->getSubject()));
    }

    /**
     * @param bool $allow
     */
    public static function allowGuestAccess(bool $allow = true)
    {
        Yii::$app
            ->getModule('user')
            ->settings
            ->set('auth.allowGuestAccess', (int)$allow);
    }

    public static function setProfileField($field, $value, $user)
    {
        if (is_int($user)) {
            $user = User::findOne($user);
        } elseif (is_string($user)) {
            $user = User::findOne(['username' => $user]);
        } elseif (!$user) {
            $user = Yii::$app->user->identity;
        }

        $user->profile->setAttributes([$field => $value]);
        $user->profile->save();
    }

    public static function becomeFriendWith($username)
    {
        $user = User::findOne(['username' => $username]);
        Friendship::add($user, Yii::$app->user->identity);
        Friendship::add(Yii::$app->user->identity, $user);
    }

    public static function follow($username)
    {
        User::findOne(['username' => $username])->follow();
    }

    public static function enableFriendships($enable = true)
    {
        Yii::$app->getModule('friendship')->settings->set('enable', $enable);
    }

    public static function setGroupPermission($groupId, $permission, $state = BasePermission::STATE_ALLOW)
    {
        $permissionManger = new PermissionManager();
        $permissionManger->setGroupState($groupId, $permission, $state);
        Yii::$app->user->permissionManager->clear();
    }

    public static function setContentContainerPermission(
        $contentContainer,
        $groupId,
        $permission,
        $state = BasePermission::STATE_ALLOW
    ) {
        $permissionManger = new ContentContainerPermissionManager(['contentContainer' => $contentContainer]);
        $permissionManger->setGroupState($groupId, $permission, $state);
        $contentContainer->permissionManager->clear();
    }

    public static function becomeUser($userName): ?User
    {
        $user = User::findOne(['username' => $userName]);
        Yii::$app->user->switchIdentity($user);
        return $user;
    }

    public static function logout()
    {
        Yii::$app->user->logout();
    }
}
