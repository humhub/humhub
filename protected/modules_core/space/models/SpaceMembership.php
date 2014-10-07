<?php

/**
 * This is the model class for table "space_membership".
 *
 * The followings are the available columns in table 'space_membership':
 * @property integer $space_id
 * @property integer $user_id
 * @property string $originator_user_id
 * @property integer $status
 * @property string $request_message
 * @property string $last_visit
 * @property integer $invite_role
 * @property integer $admin_role
 * @property integer $share_role
 * @property string $created_at
 * @property integer $created_by
 * @property string $updated_at
 * @property integer $updated_by
 *
 * @package humhub.modules_core.user.models
 * @since 0.5
 * @author Luke

 */
class SpaceMembership extends HActiveRecord
{

    const STATUS_INVITED = 1;
    const STATUS_APPLICANT = 2;
    const STATUS_MEMBER = 3;

    /**
     * Returns the static model of the specified AR class.
     * @param string $className active record class name.
     * @return SpaceMembership the static model class
     */
    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    /**
     * @return string the associated database table name
     */
    public function tableName()
    {
        return 'space_membership';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        // NOTE: you should only define rules for those attributes that
        // will receive user inputs.
        return array(
            array('space_id, user_id', 'required'),
            array('space_id, user_id, status, invite_role, admin_role, share_role, created_by, updated_by', 'numerical', 'integerOnly' => true),
            array('originator_user_id', 'length', 'max' => 45),
            array('request_message, last_visit, created_at, updated_at', 'safe'),
        );
    }

    /**
     * @return array relational rules.
     */
    public function relations()
    {
        // NOTE: you may need to adjust the relation name and the related
        // class name for the relations automatically generated below.
        return array(
            'user' => array(self::BELONGS_TO, 'User', 'user_id'),
            'space' => array(self::BELONGS_TO, 'Space', 'space_id'),
            // deprecated
            'workspace' => array(self::BELONGS_TO, 'Space', 'space_id'),
        );
    }

    /**
     * Before saving this record.
     *
     * @return type
     */
    protected function beforeSave()
    {
        Yii::app()->cache->delete('userSpaces_' . $this->user_id);
        return parent::beforeSave();
    }

    /**
     * Before delete record
     *
     * @return type
     */
    protected function beforeDelete()
    {
        Yii::app()->cache->delete('userSpaces_' . $this->user_id);
        return parent::beforeDelete();
    }

    /**
     * Update last visit
     */
    public function updateLastVisit()
    {
        $this->last_visit = new CDbExpression('NOW()');
        $this->saveAttributes(array('last_visit'));
    }

    /**
     * Counts all new Items for this membership
     */
    public function countNewItems($since = "")
    {

        $count = 0;

        $connection = Yii::app()->db;

        // Count new Wall Entries
        $sql = "SELECT COUNT(*) FROM wall_entry " .
                "LEFT JOIN content ON wall_entry.content_id = content.id " .
                "WHERE content.object_model!='Activity' AND wall_entry.wall_id=:wall_id AND wall_entry.created_at>:last_visit";

        $wallId = $this->workspace->wall_id;
        $lastVisit = $this->last_visit;
        $command = $connection->createCommand($sql);
        $command->bindParam(":wall_id", $wallId);
        $command->bindParam(":last_visit", $lastVisit);
        $count += $command->queryScalar();

        // Count new comments
        $sql = "SELECT COUNT(*) FROM comment WHERE space_id=:space_id AND created_at>:last_visit";
        $workspaceId = $this->workspace->id;
        $lastVisit = $this->last_visit;
        $command = $connection->createCommand($sql);
        $command->bindParam(":space_id", $workspaceId);
        $command->bindParam(":last_visit", $lastVisit);
        $count += $command->queryScalar();

        return $count;
    }

    /**
     * Returns a list of all spaces of the given userId
     *
     * @param type $userId
     */
    public static function GetUserSpaces($userId = "")
    {

        // Take current userid if none is given
        if ($userId == "")
            $userId = Yii::app()->user->id;

        $cacheId = "userSpaces_" . $userId;
        $cacheValue = Yii::app()->cache->get($cacheId);
        $orderSetting = HSetting::Get('spaceOrder', 'space');

        if ($cacheValue === false) {
            $criteria = new CDbCriteria();

            if ($orderSetting == 0) {
                $criteria->order = 'name ASC';
            } else {
                $criteria->order = 'last_visit DESC';
            }

            $spaces = array();
            $memberships = SpaceMembership::model()->with('space')->findAllByAttributes(array(
                'user_id' => $userId,
                'status' => SpaceMembership::STATUS_MEMBER,
                    ), $criteria);

            foreach ($memberships as $membership) {
                $spaces[] = $membership->space;
            }

            Yii::app()->cache->set($cacheId, $spaces, HSetting::Get('expireTime', 'cache'));
            return $spaces;
        } else {
            return $cacheValue;
        }
    }

}
