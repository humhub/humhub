<?php

namespace humhub\modules\notification\models;

use Yii;
use yii\db\Expression;

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
 * @property string $created_at
 * @property integer $desktop_notified
 * @property integer $originator_user_id
 * @property integer $send_web_notifications
 */
class Notification extends \humhub\components\ActiveRecord
{

    /**
     * @var int number of found grouped notifications
     */
    public $group_count;

    /*
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
                'class' => \humhub\components\behaviors\PolymorphicRelation::className(),
                'classAttribute' => 'source_class',
                'pkAttribute' => 'source_pk',
                'mustBeInstanceOf' => [
                    \yii\db\ActiveRecord::className(),
                ]
            ]
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
            [['user_id', 'seen', 'source_pk', 'space_id', 'emailed', 'desktop_notified', 'originator_user_id'], 'integer'],
            [['class', 'source_class'], 'string', 'max' => 100]
        ];
    }

    /**
     * Use getBaseModel instead.
     * @deprecated since version 1.2
     * @param type $params
     */
    public function getClass($params = [])
    {
        return $this->getBaseModel($params);
    }

    /**
     * Returns the business model of this notification
     *
     * @return \humhub\modules\notification\components\BaseNotification
     */
    public function getBaseModel($params = [])
    {
        if (class_exists($this->class)) {
            $params['source'] = $this->getPolymorphicRelation();
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
     * @return \yii\db\ActiveQuery the receiver of this notification
     */
    public function getUser()
    {
        return $this->hasOne(\humhub\modules\user\models\User::className(), ['id' => 'user_id']);
    }

    /**
     * @return \yii\db\ActiveQuery the originator user relations
     */
    public function getOriginator()
    {
        return $this->hasOne(\humhub\modules\user\models\User::className(), ['id' => 'originator_user_id']);
    }

    /**
     * Returns space of this notification
     *
     * @deprecated since version 1.1
     * @return type
     */
    public function getSpace()
    {
        return $this->hasOne(\humhub\modules\space\models\Space::className(), ['id' => 'space_id']);
    }

    /**
     * Returns polymorphic relation linked with this notification
     *
     * @return \humhub\components\ActiveRecord
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
     */
    public static function getModuleNotifications()
    {
        $result = [];
        foreach (Yii::$app->moduleManager->getModules(['includeCoreModules' => true]) as $module) {
            if ($module instanceof \humhub\components\Module) {
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
        return (new \yii\db\Query())
                        ->select(['class'])
                        ->from(self::tableName())
                        ->distinct()->all();
    }

    /**
     * Loads a certain amount ($limit) of grouped notifications from a given id set by $from.
     *
     * @param integer $from notificatoin id which was the last loaded entry.
     * @param limit $limit limit count of results.
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
     * @return \yii\db\ActiveQuery
     */
    public static function findGrouped(User $user = null, $sendWebNotifications = 1)
    {
        $user = ($user) ? $user : Yii::$app->user->getIdentity();

        $query = self::find();
        $query->addSelect(['notification.*',
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
     * @param \humhub\modules\notification\models\User $user
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
     * @param \humhub\modules\notification\models\User $user
     *  @since 1.2
     */
    public static function findUnnotifiedInFrontend(User $user = null)
    {
        return self::findUnseen($user)->andWhere(['desktop_notified' => 0]);
    }

}
