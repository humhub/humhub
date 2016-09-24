<?php

namespace humhub\modules\notification\models;

use Yii;

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
 */
class Notification extends \humhub\components\ActiveRecord
{

    /**
     * @var int number of found grouped notifications
     */
    public $group_count;

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
     * Returns the business model of this notification
     * 
     * @return \humhub\modules\notification\components\BaseNotification
     */
    public function getClass($params = [])
    {
        if (class_exists($this->class)) {
            $params['source'] = $this->getSourceObject();
            $params['originator'] = $this->originator;
            $params['groupCount'] = $this->group_count;
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
     * Finds notifications grouped when available
     * 
     * @return \yii\db\ActiveQuery
     */
    public static function findGrouped()
    {
        $query = self::find();
        $query->addSelect(['notification.*', 
            new \yii\db\Expression('count(distinct(originator_user_id)) as group_count'),
            new \yii\db\Expression('max(created_at) as group_created_at'),
            new \yii\db\Expression('min(seen) as group_seen'),
        ]);
        $query->addGroupBy(['COALESCE(group_key, id)', 'class']);
        $query->orderBy(['group_seen' => SORT_ASC, 'group_created_at' => SORT_DESC]);

        return $query;
    }

}
