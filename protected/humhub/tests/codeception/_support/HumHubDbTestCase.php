<?php

namespace tests\codeception\_support;

use Codeception\Test\Unit;
use humhub\components\behaviors\PolymorphicRelation;
use humhub\libs\BasePermission;
use Codeception\Configuration;
use Codeception\Exception\ModuleException;
use Codeception\Module;
use Codeception\Module\Yii2;
use humhub\models\UrlOembed;
use humhub\modules\activity\tests\codeception\fixtures\ActivityFixture;
use humhub\modules\content\tests\codeception\fixtures\ContentContainerFixture;
use humhub\modules\content\tests\codeception\fixtures\ContentFixture;
use humhub\modules\content\widgets\richtext\converter\RichTextToHtmlConverter;
use humhub\modules\content\widgets\richtext\converter\RichTextToMarkdownConverter;
use humhub\modules\content\widgets\richtext\converter\RichTextToPlainTextConverter;
use humhub\modules\content\widgets\richtext\converter\RichTextToShortTextConverter;
use humhub\modules\file\tests\codeception\fixtures\FileFixture;
use humhub\modules\file\tests\codeception\fixtures\FileHistoryFixture;
use humhub\modules\friendship\tests\codeception\fixtures\FriendshipFixture;
use humhub\modules\live\tests\codeception\fixtures\LiveFixture;
use humhub\modules\notification\tests\codeception\fixtures\NotificationFixture;
use humhub\modules\space\tests\codeception\fixtures\SpaceFixture;
use humhub\modules\space\tests\codeception\fixtures\SpaceMembershipFixture;
use humhub\modules\user\tests\codeception\fixtures\GroupPermissionFixture;
use humhub\modules\user\tests\codeception\fixtures\UserFullFixture;
use humhub\tests\codeception\fixtures\SettingFixture;
use humhub\tests\codeception\fixtures\UrlOembedFixture;
use PHPUnit\Framework\SkippedTestError;
use TypeError;
use Yii;
use yii\db\ActiveRecord;
use humhub\modules\activity\models\Activity;
use humhub\modules\content\components\ContentContainerPermissionManager;
use humhub\modules\notification\models\Notification;
use humhub\modules\user\components\PermissionManager;
use humhub\modules\user\models\User;
use humhub\modules\friendship\models\Friendship;
use yii\db\Command;
use yii\db\Exception;
use yii\db\ExpressionInterface;
use yii\db\Query;

/**
 * @SuppressWarnings(PHPMD)
 */
class HumHubDbTestCase extends Unit
{
    use HumHubHelperTrait;

    protected $fixtureConfig;

    public $appConfig = '@tests/codeception/config/unit.php';

    public $time;


    protected function setUp(): void
    {
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
        $this->initModules(__METHOD__);
        $this->reloadSettings(__METHOD__);
        $this->flushCache(__METHOD__);
        $this->deleteMails(__METHOD__);

        parent::setUp();
    }

    /**
     * Initializes modules defined in @tests/codeception/config/test.config.php
     * Note the config key in test.config.php is modules and not humhubModules!
     */
    protected function initModules(?string $caller = null)
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
                    $result = array_merge($result, $this->getDefaultFixtures());
                } else {
                    $result[$fixtureTable] = ['class' => $fixtureClass];
                }
            }
        }

        return $result;
    }

    protected function getDefaultFixtures(): array
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

    public static function assertHasNotification($class, ActiveRecord $source, $originator_id = null, $target_id = null, $msg = '')
    {
        $notificationQuery = Notification::find()->where([
            'class' => $class,
            'source_class' => PolymorphicRelation::getObjectModel($source),
            'source_pk' => $source->getPrimaryKey(),
        ]);
        if (is_string($target_id)) {
            $msg = $target_id;
            $target_id = null;
        }

        if ($originator_id != null) {
            $notificationQuery->andWhere(['originator_user_id' => $originator_id]);
        }

        if ($target_id != null) {
            $notificationQuery->andWhere(['user_id' => $target_id]);
        }

        static::assertNotEmpty($notificationQuery->all(), $msg);
    }

    public static function assertEqualsNotificationCount($count, $class, ActiveRecord $source, $originator_id = null, $target_id = null, $msg = '')
    {
        $notificationQuery = Notification::find()->where(['class' => $class, 'source_class' => PolymorphicRelation::getObjectModel($source), 'source_pk' => $source->getPrimaryKey()]);

        if ($originator_id != null) {
            $notificationQuery->andWhere(['originator_user_id' => $originator_id]);
        }

        if ($target_id != null) {
            $notificationQuery->andWhere(['user_id' => $target_id]);
        }

        static::assertEquals($count, $notificationQuery->count(), $msg);
    }

    public static function assertHasNoNotification($class, ActiveRecord $source, $originator_id = null, $target_id = null, $msg = '')
    {
        $notificationQuery = Notification::find()->where(['class' => $class, 'source_class' => PolymorphicRelation::getObjectModel($source), 'source_pk' => $source->getPrimaryKey()]);

        if ($originator_id != null) {
            $notificationQuery->andWhere(['originator_user_id' => $originator_id]);
        }

        if ($target_id != null) {
            $notificationQuery->andWhere(['user_id' => $target_id]);
        }

        static::assertEmpty($notificationQuery->all(), $msg);
    }

    public static function assertHasActivity($class, ActiveRecord $source, $msg = '')
    {
        $activity = Activity::findOne([
            'class' => $class,
            'object_model' => PolymorphicRelation::getObjectModel($source),
            'object_id' => $source->getPrimaryKey(),
        ]);
        static::assertNotNull($activity, $msg);
    }

    /**
     * @return Yii2|Module
     * @throws ModuleException
     */
    public function getYiiModule()
    {
        return $this->getModule('Yii2');
    }

    /**
     * @see assertSentEmail
     * @since 1.3
     */
    public function assertMailSent($count = 0)
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $this->getYiiModule()->seeEmailIsSent($count);
    }

    /**
     * @param int $count
     *
     * @throws ModuleException
     * @since 1.3
     */
    public function assertSentEmail(int $count = 0)
    {
        $this->getYiiModule()->seeEmailIsSent($count);
    }

    public function assertEqualsLastEmailTo($to, $strict = true)
    {
        if (is_string($to)) {
            $to = [$to];
        }

        /** @noinspection PhpUnhandledExceptionInspection */
        $message = $this->getYiiModule()->grabLastSentEmail();
        $expected = $message->getTo();

        foreach ($to as $email) {
            $this->assertArrayHasKey($email, $expected);
        }

        if ($strict) {
            $this->assertCount(count($expected), $to);
        }
    }

    public function assertEqualsLastEmailSubject($subject)
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $message = $this->getYiiModule()->grabLastSentEmail();
        $this->assertEquals($subject, str_replace(["\n", "\r"], '', $message->getSubject()));
    }

    /**
     * @param int|null $expected Number of records expected. Null for any number, but not none
     * @param string|array|ExpressionInterface $tables
     * @param string|array|ExpressionInterface|null $condition
     * @param array|null $params
     * @param string $message
     *
     * @return void
     * @since 1.15
     */
    public static function assertRecordCount(?int $expected, $tables, $condition = null, ?array $params = [], string $message = ''): void
    {
        $count = static::dbCount($tables, $condition, $params ?? []);

        if ($expected === null) {
            static::assertGreaterThan(0, $count, $message);
        } else {
            static::assertEquals($expected, $count, $message);
        }
    }

    /**
     * @param string|array|ExpressionInterface $tables
     * @param string|array|ExpressionInterface|null $condition
     * @param array|null $params
     * @param string $message
     *
     * @return void
     * @since 1.15
     */
    public static function assertRecordExistsAny($tables, $condition = null, ?array $params = [], string $message = 'Record does not exist'): void
    {
        static::assertRecordCount(null, $tables, $condition, $params ?? [], $message);
    }

    /**
     * @param string|array|ExpressionInterface $tables
     * @param string|array|ExpressionInterface|null $condition
     * @param array|null $params
     * @param string $message
     *
     * @return void
     * @since 1.15
     */
    public static function assertRecordExists($tables, $condition = null, ?array $params = [], string $message = 'Record does not exist'): void
    {
        static::assertRecordCount(1, $tables, $condition, $params ?? [], $message);
    }

    /**
     * @param string|array|ExpressionInterface $tables
     * @param string|array|ExpressionInterface|null $condition
     * @param array|null $params
     * @param string $message
     *
     * @return void
     * @since 1.15
     */
    public static function assertRecordNotExists($tables, $condition = null, ?array $params = [], string $message = 'Record exists'): void
    {
        static::assertRecordCount(0, $tables, $condition, $params ?? [], $message);
    }

    /**
     * @param int|string|null $expected Number of records expected. Null for any number, but not none
     * @param string $column
     * @param string|array|ExpressionInterface $tables
     * @param string|array|ExpressionInterface|null $condition
     * @param array|null $params
     * @param string $message
     *
     * @return void
     * @since 1.15
     */
    public static function assertRecordValue($expected, string $column, $tables, $condition = null, ?array $params = [], string $message = ''): void
    {
        $value = static::dbQuery($tables, $condition, $params, 1)->select($column)->scalar();
        static::assertEquals($expected, $value, $message);
    }

    public function expectExceptionTypeError(string $calledClass, string $method, int $argumentNumber, string $argumentName, string $expectedType, string $givenTye, string $exceptionClass = TypeError::class): void
    {
        $this->expectException($exceptionClass);

        $calledClass = str_replace('\\', '\\\\', $calledClass);
        $argumentName = ltrim($argumentName, '$');

        $this->expectExceptionMessageRegExp(
            sprintf(
            // Php < 8 uses: "Argument n passed to class::method() ..."
            // PHP > 7 uses: "class::method(): Argument #n ($argument) ..."
                '@^((Argument %d passed to )?%s::%s\\(\\)(?(2)|: Argument #%d \\(\\$%s\\))) must be of( the)? type %s, %s given, called in /.*@',
                $argumentNumber,
                $calledClass,
                $method,
                $argumentNumber,
                $argumentName,
                $expectedType,
                $givenTye
            )
        );
    }

    /**
     * @param bool $allow
     */
    public function allowGuestAccess(bool $allow = true)
    {
        Yii::$app
            ->getModule('user')
            ->settings
            ->set('auth.allowGuestAccess', (int)$allow);
    }

    public function setProfileField($field, $value, $user)
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

    public function becomeFriendWith($username)
    {
        $user = User::findOne(['username' => $username]);
        Friendship::add($user, Yii::$app->user->identity);
        Friendship::add(Yii::$app->user->identity, $user);
    }

    public function follow($username)
    {
        User::findOne(['username' => $username])->follow();
    }

    public function enableFriendships($enable = true)
    {
        Yii::$app->getModule('friendship')->settings->set('enable', $enable);
    }

    public function setGroupPermission($groupId, $permission, $state = BasePermission::STATE_ALLOW)
    {
        $permissionManger = new PermissionManager();
        $permissionManger->setGroupState($groupId, $permission, $state);
        Yii::$app->user->permissionManager->clear();
    }

    public function setContentContainerPermission(
        $contentContainer,
        $groupId,
        $permission,
        $state = BasePermission::STATE_ALLOW
    ) {
        $permissionManger = new ContentContainerPermissionManager(['contentContainer' => $contentContainer]);
        $permissionManger->setGroupState($groupId, $permission, $state);
        $contentContainer->permissionManager->clear();
    }

    public function becomeUser($userName): ?User
    {
        $user = User::findOne(['username' => $userName]);
        Yii::$app->user->switchIdentity($user);
        return $user;
    }

    public function logout()
    {
        Yii::$app->user->logout();
    }

    /**
     * @see \yii\db\Connection::createCommand()
     * @since 1.15
     */
    public static function dbCommand($sql = null, $params = []): Command
    {
        return Yii::$app->getDb()->createCommand($sql, $params);
    }

    /**
     * @param Command $cmd
     * @param bool $execute
     *
     * @return Command
     * @throws Exception
     */
    protected static function dbCommandExecute(Command $cmd, bool $execute = true): Command
    {
        if ($execute) {
            $cmd->execute();
        }

        return $cmd;
    }

    /**
     * @see Query
     * @since 1.15
     */
    public static function dbQuery($tables, $condition, $params = [], $limit = 10): Query
    {
        return (new Query())
            ->from($tables)
            ->where($condition, $params)
            ->limit($limit);
    }

    /**
     * @see Command::insert
     * @since 1.15
     */
    public static function dbInsert($table, $columns, bool $execute = true): Command
    {
        return static::dbCommandExecute(static::dbCommand()->insert($table, $columns), $execute);
    }

    /**
     * @see Command::update
     * @since 1.15
     */
    public static function dbUpdate($table, $columns, $condition = '', $params = [], bool $execute = true): Command
    {
        return static::dbCommandExecute(static::dbCommand()->update($table, $columns, $condition, $params), $execute);
    }

    /**
     * @see Command::upsert
     * @since 1.15
     */
    public static function dbUpsert($table, $insertColumns, $updateColumns = true, $params = [], bool $execute = true): Command
    {
        return static::dbCommandExecute(static::dbCommand()->upsert($table, $insertColumns, $updateColumns, $params), $execute);
    }

    /**
     * @see Command::delete()
     * @since 1.15
     */
    public static function dbDelete($table, $condition = '', $params = [], bool $execute = true): Command
    {
        return static::dbCommandExecute(static::dbCommand()->delete($table, $condition, $params), $execute);
    }

    /**
     * @see Query::select
     * @see Query::from
     * @see Query::where
     * @see \yii\db\QueryTrait::limit()
     * @since 1.15
     */
    public static function dbSelect($tables, $columns, $condition = '', $params = [], $limit = 10, $selectOption = null): array
    {
        return static::dbQuery($tables, $condition, $params, $limit)
            ->select($columns, $selectOption)
            ->all();
    }

    /**
     * @see Command::delete()
     * @since 1.15
     */
    public static function dbCount($tables, $condition = '', $params = [])
    {
        return static::dbQuery($tables, $condition, $params)
            ->select("count(*)")
            ->scalar();
    }
}
