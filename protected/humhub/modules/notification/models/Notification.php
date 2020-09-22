<?php

namespace humhub\modules\notification\models;

use humhub\components\ActiveRecord;
use humhub\components\behaviors\PolymorphicRelation;
use humhub\components\Module;
use humhub\modules\notification\components\BaseNotification;
use humhub\modules\user\models\User;
use Yii;
use yii\base\Exception;
use yii\db\ActiveQuery;
use yii\db\Expression;
use yii\db\IntegrityException;
use yii\db\Query;

/**
 * This is the model class for table "notification".
 *
 * @property integer $id
 * @property string $class
 * @property integer $user_id
 * @property integer $seen
 * @property string $source_class
 * @property integer $source_pk
 * @property integer $space_id
 * @property integer $emailed
 * @property string module
 * @property string $created_at
 * @property integer $desktop_notified
 * @property integer $originator_user_id
 * @property integer $send_web_notifications
 * @property User|null $originator
 * @property User $user
 *
 * @mixin PolymorphicRelation
 */
class Notification extends ActiveRecord
{

    /**
     * @var int number of found grouped notifications
     */
    public $group_count;

    /**
     * @var int number of involved users of grouped notifications
     */
    public $group_user_count;

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            [
                'class' => PolymorphicRelation::class,
                'classAttribute' => 'source_class',
                'pkAttribute' => 'source_pk',
                'strict' => true,
                'mustBeInstanceOf' => [
                    \yii\db\ActiveRecord::class,
                ],
            ],
        ];
    }

    public function init()
    {
        parent::init();
        if ($this->seen === null) {
            $this->seen = 0;
        }

        // Disable web notification by default, they will be enabld within the web target if allowed by the user.
        if ($this->send_web_notifications === null) {
            $this->send_web_notifications = 0;
        }
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'notification';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['class', 'user_id'], 'required'],
            [
                ['user_id', 'seen', 'source_pk', 'space_id', 'emailed', 'desktop_notified', 'originator_user_id'],
                'integer',
            ],
            [['class', 'source_class'], 'string', 'max' => 100],
        ];
    }

    /**
     * Use getBaseModel instead.
     * @param array $params
     * @return BaseNotification
     * @throws IntegrityException
     * @deprecated since version 1.2 use [getBaseModel()] instead
     */
    public function getClass($params = [])
    {
        return $this->getBaseModel($params);
    }

    /**
     * Returns the business model of this notification
     *
     * @param array $params
     * @return BaseNotification
     * @throws IntegrityException
     */
    public function getBaseModel($params = [])
    {
        if (class_exists($this->class)) {
            try {
                $params['source'] = $this->getPolymorphicRelation();
            } catch (IntegrityException $e) {
                $params['source'] = null;
            }
            $params['originator'] = $this->originator;
            $params['groupCount'] = $this->group_user_count;
            if ($this->group_count > 1) {
                // Make sure we're loaded the latest notification record
                $params['record'] = self::find()
                    ->orderBy(['seen' => SORT_ASC, 'created_at' => SORT_DESC])
                    ->andWhere(['class' => $this->class, 'user_id' => $this->user_id, 'group_key' => $this->group_key])
                    ->one();
                $params['originator'] = $params['record']->originator;

            } else {
                $params['record'] = $this;
            }

            $object = new $this->class;
            Yii::configure($object, $params);
            return $object;
        }
        return null;
    }

    /**
     * @return ActiveQuery the receiver of this notification
     */
    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    /**
     * @return ActiveQuery the originator user relations
     */
    public function getOriginator()
    {
        return $this->hasOne(User::class, ['id' => 'originator_user_id']);
    }

    /**
     * Returns polymorphic relation linked with this notification
     *
     * @return ActiveRecord
     */
    public function getSourceObject()
    {
        $sourceClass = $this->source_class;
        if (class_exists($sourceClass) && $sourceClass != "") {
            return $sourceClass::findOne(['id' => $this->source_pk]);
        }
        return null;
    }

    /**
     * Returns all available notifications of a module identified by its modulename.
     *
     * @return array with format [moduleId => notifications[]]
     * @throws Exception
     */
    public static function getModuleNotifications()
    {
        $result = [];
        foreach (Yii::$app->moduleManager->getModules(['includeCoreModules' => true]) as $module) {
            if ($module instanceof Module) {
                $notifications = $module->getNotifications();
                if (count($notifications) > 0) {
                    $result[$module->getName()] = $notifications;
                }
            }
        }
        return $result;
    }

    /**
     * Returns a distinct list of notification classes already in the database.
     */
    public static function getNotificationClasses()
    {
        return (new Query())
            ->select(['class'])
            ->from(self::tableName())
            ->distinct()->all();
    }

    /**
     * Loads a certain amount ($limit) of grouped notifications from a given id set by $from.
     *
     * @param integer $from notification id which was the last loaded entry.
     * @param int $limit count of results.
     * @return Notification[]
     * @throws \Throwable
     * @since 1.2
     */
    public static function loadMore($from = 0, $limit = 6)
    {
        $query = Notification::findGrouped();

        if ($from != 0) {
            $query->andWhere(['<', 'id', $from]);
        }

        $query->limit($limit);

        return $query->all();
    }

    /**
     * Finds grouped notifications if $sendWebNotifications is set to 1 we filter only notifications
     * with send_web_notifications setting to 1.
     *
     * @param User|null $user
     * @param int $sendWebNotifications
     * @return ActiveQuery
     * @throws \Throwable
     */
    public static function findGrouped(User $user = null, $sendWebNotifications = 1)
    {
        $user = ($user) ? $user : Yii::$app->user->getIdentity();

        $query = self::find();
        $query->addSelect([
            'notification.*',
            new Expression('count(distinct(originator_user_id)) as group_user_count'),
            new Expression('count(*) as group_count'),
            new Expression('max(created_at) as group_created_at'),
            new Expression('min(seen) as group_seen'),
        ]);

        $query->andWhere(['user_id' => $user->id]);

        $query->andWhere(['send_web_notifications' => $sendWebNotifications]);
        $query->addGroupBy([
            'COALESCE(group_key, id)',
            'class',
        ]);
        $query->orderBy(['group_seen' => SORT_ASC, 'group_created_at' => SORT_DESC]);

        return $query;
    }

    /**
     * Finds all grouped unseen notifications for the given user or the current loggedIn user
     * if no User instance is provided.
     *
     * @param User $user
     * @return ActiveQuery
     * @throws \Throwable
     * @since 1.2
     */
    public static function findUnseen(User $user = null)
    {
        return Notification::findGrouped($user)
            ->andWhere(['seen' => 0])
            ->orWhere(['IS', 'seen', new Expression('NULL')]);
    }

    /**
     * Finds all grouped unseen notifications which were not already sent to the frontend.
     *
     * @param User $user
     * @return ActiveQuery
     * @throws \Throwable
     * @since 1.2
     */
    public static function findUnnotifiedInFrontend(User $user = null)
    {
        return self::findUnseen($user)->andWhere(['desktop_notified' => 0]);
    }

}
