<?php

namespace humhub\modules\space\models;

use Yii;
use humhub\components\ActiveRecord;
use humhub\modules\user\models\User;
use humhub\modules\content\models\WallEntry;
use humhub\modules\activity\models\Activity;


/**
 * This is the model class for table "space_membership".
 *
 * @property integer $space_id
 * @property integer $user_id
 * @property string $originator_user_id
 * @property integer $status
 * @property string $request_message
 * @property string $last_visit
 * @property integer $show_at_dashboard
 * @property boolean $can_leave
 * @property string $group_id
 * @property string $created_at
 * @property integer $created_by
 * @property string $updated_at
 * @property integer $updated_by
 */
class Membership extends ActiveRecord
{

    const STATUS_INVITED = 1;
    const STATUS_APPLICANT = 2;
    const STATUS_MEMBER = 3;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'space_membership';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['space_id', 'user_id'], 'required'],
            [['space_id', 'user_id', 'originator_user_id', 'status'], 'integer'],
            [['request_message'], 'string'],
            [['last_visit', 'group_id'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'space_id' => 'Space ID',
            'user_id' => 'User ID',
            'originator_user_id' => Yii::t('SpaceModule.models_Membership', 'Originator User ID'),
            'status' => Yii::t('SpaceModule.models_Membership', 'Status'),
            'request_message' => Yii::t('SpaceModule.models_Membership', 'Request Message'),
            'last_visit' => Yii::t('SpaceModule.models_Membership', 'Last Visit'),
            'created_at' => Yii::t('SpaceModule.models_Membership', 'Created At'),
            'created_by' => Yii::t('SpaceModule.models_Membership', 'Created By'),
            'updated_at' => Yii::t('SpaceModule.models_Membership', 'Updated At'),
            'updated_by' => Yii::t('SpaceModule.models_Membership', 'Updated By'),
            'can_leave' => 'Can Leave',
        ];
    }

    public function getUser()
    {
        return $this->hasOne(\humhub\modules\user\models\User::className(), ['id' => 'user_id']);
    }

    public function getOriginator()
    {
        return $this->hasOne(\humhub\modules\user\models\User::className(), ['id' => 'originator_user_id']);
    }

    public function getSpace()
    {
        return $this->hasOne(\humhub\modules\space\models\Space::className(), ['id' => 'space_id']);
    }

    public function beforeSave($insert)
    {
        Yii::$app->cache->delete('userSpaces_' . $this->user_id);
        return parent::beforeSave($insert);
    }

    public function beforeDelete()
    {
        Yii::$app->cache->delete('userSpaces_' . $this->user_id);
        return parent::beforeDelete();
    }

    /**
     * Update last visit
     */
    public function updateLastVisit()
    {
        $this->last_visit = new \yii\db\Expression('NOW()');
        $this->update(false, ['last_visit']);
    }

    /**
     * Counts all new Items for this membership
     */
    public function countNewItems($since = "")
    {
        $query = WallEntry::find()->joinWith('content');
        $query->where(['!=', 'content.object_model', Activity::className()]);
        $query->andWhere(['wall_entry.wall_id' => $this->space->wall_id]);
        $query->andWhere(['>', 'wall_entry.created_at', $this->last_visit]);
        $count = $query->count();
        return $count;
    }

    /**
     * Returns a list of all spaces of the given userId
     *
     * @param type $userId
     */
    public static function GetUserSpaces($userId = "")
    {
        if ($userId == "")
            $userId = Yii::$app->user->id;

        $cacheId = "userSpaces_" . $userId;

        $spaces = Yii::$app->cache->get($cacheId);
        if ($spaces === false) {

            $orderSetting = Yii::$app->getModule('space')->settings->get('spaceOrder');
            $orderBy = 'name ASC';
            if ($orderSetting != 0) {
                $orderBy = 'last_visit DESC';
            }
            $memberships = self::find()->joinWith('space')->where(['user_id' => $userId, 'space_membership.status' => self::STATUS_MEMBER])->orderBy($orderBy);

            $spaces = array();
            foreach ($memberships->all() as $membership) {
                $spaces[] = $membership->space;
            }
            Yii::$app->cache->set($cacheId, $spaces);
        }
        return $spaces;
    }

    /**
     * Returns Space for user space membership
     *
     * @since 1.0
     * @param \humhub\modules\user\models\User $user
     * @param boolean $memberOnly include only member status - no pending/invite states
     * @return \yii\db\ActiveQuery for space model
     */
    public static function getUserSpaceQuery($user, $memberOnly = true)
    {
        $query = Space::find();
        $query->leftJoin('space_membership', 'space_membership.space_id=space.id and space_membership.user_id=:userId', [':userId' => $user->id]);

        if ($memberOnly) {
            $query->andWhere(['space_membership.status' => self::STATUS_MEMBER]);
        }

        $query->orderBy(['name' => SORT_ASC]);

        return $query;
    }

    /**
     * Returns a user query for space memberships
     * 
     * @since 1.1
     * @param Space $space
     * @param boolean $membersOnly Only return approved members
     * @return \humhub\modules\user\components\ActiveQueryUser
     */
    public static function getSpaceMembersQuery($space, $membersOnly = true)
    {
        $query = User::find()->active();
        $query->join('LEFT JOIN', 'space_membership', 'space_membership.user_id=user.id');
        if ($membersOnly) {
            $query->andWhere(['space_membership.status' => self::STATUS_MEMBER]);
        }
        $query->andWhere(['space_id' => $space->id]);
        $query->defaultOrder();
        return $query;
    }

}
