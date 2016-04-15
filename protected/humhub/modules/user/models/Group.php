<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2016 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\models;

use Yii;
use humhub\modules\space\models\Space;
use humhub\modules\user\models\User;

/**
 * This is the model class for table "group".
 *
 * @property integer $id
 * @property integer $space_id
 * @property string $name
 * @property string $description
 * @property string $created_at
 * @property integer $created_by
 * @property string $updated_at
 * @property integer $updated_by
 */
class Group extends \yii\db\ActiveRecord
{

    const SCENARIO_EDIT = 'edit';

    public $managerGuids;
    public $defaultSpaceGuid;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'group';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['managerGuids', 'name'], 'required', 'on' => self::SCENARIO_EDIT],
            ['managerGuids', 'atleasOneAdminCheck', 'on' => self::SCENARIO_EDIT],
            [['space_id', 'created_by', 'updated_by'], 'integer'],
            [['description', 'managerGuids', 'defaultSpaceGuid'], 'string'],
            [['created_at', 'updated_at'], 'safe'],
            [['name'], 'string', 'max' => 45]
        ];
    }

    public function scenarios()
    {
        $scenarios = parent::scenarios();
        $scenarios[self::SCENARIO_EDIT] = ['name', 'description', 'managerGuids', 'defaultSpaceGuid', 'show_at_registration', 'show_at_directory'];
        return $scenarios;
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'space_id' => 'Space ID',
            'name' => 'Name',
            'managerGuids' => 'Manager',
            'description' => 'Description',
            'created_at' => 'Created At',
            'created_by' => 'Created By',
            'updated_at' => 'Updated At',
            'updated_by' => 'Updated By'
        ];
    }

    public function atleasOneAdminCheck()
    {
        return !$this->show_at_registration || count(explode(",", $this->managerGuids) > 0);
    }

    public function beforeSave($insert)
    {

        // When on edit form scenario, save also defaultSpaceGuid/managerGuids
        if ($this->scenario == self::SCENARIO_EDIT) {
            if ($this->defaultSpaceGuid == "") {
                $this->space_id = "";
            } else {
                $space = \humhub\modules\space\models\Space::findOne(['guid' => rtrim($this->defaultSpaceGuid, ',')]);
                if ($space !== null) {
                    $this->space_id = $space->id;
                }
            }
        }

        return parent::beforeSave($insert);
    }

    /**
     * @inheritdoc
     */
    public function afterSave($insert, $changedAttributes)
    {
        if ($this->scenario == self::SCENARIO_EDIT) {
            $managerGuids = explode(",", $this->managerGuids);
            foreach ($managerGuids as $managerGuid) {
                // Ensure guids valid characters
                $managerGuid = preg_replace("/[^A-Za-z0-9\-]/", '', $managerGuid);

                // Try to load user and get/create the GroupUser relation with isManager
                $user = \humhub\modules\user\models\User::findOne(['guid' => $managerGuid]);
                if ($user != null) {
                    $groupUser = GroupUser::findOne(['group_id' => $this->id, 'user_id' => $user->id]);
                    if ($groupUser != null && !$groupUser->is_group_manager) {
                        $groupUser->is_group_manager = true;
                        $groupUser->save();
                    } else {
                        $this->addUser($user, true);
                    }
                }
            }

            //Remove admins not contained in the selection
            foreach ($this->getManager()->all() as $admin) {
                if (!in_array($admin->guid, $managerGuids)) {
                    $groupUser = GroupUser::findOne(['group_id' => $this->id, 'user_id' => $admin->id]);
                    if ($groupUser != null) {
                        $groupUser->is_group_manager = false;
                        $groupUser->save();
                    }
                }
            }
        }

        parent::afterSave($insert, $changedAttributes);
    }

    public function populateDefaultSpaceGuid()
    {
        $defaultSpace = Space::findOne(['id' => $this->space_id]);
        if ($defaultSpace !== null) {
            $this->defaultSpaceGuid = $defaultSpace->guid;
        }
    }

    public function populateManagerGuids()
    {
        $this->managerGuids = "";
        foreach ($this->manager as $manager) {
            $this->managerGuids .= $manager->guid . ",";
        }
    }

    /**
     * Returns the admin group.
     * @return type
     */
    public static function getAdminGroup()
    {
        return self::findOne(['is_admin_group' => '1']);
    }

    /**
     * Returns all user which are defined as manager in this group as ActiveQuery.
     * @return ActiveQuery
     */
    public function getManager()
    {
        return $this->hasMany(User::className(), ['id' => 'user_id'])
                        ->via('groupUsers', function($query) {
                            $query->where(['is_group_manager' => '1']);
                        });
    }

    /**
     * Checks if this group has at least one Manager assigned.
     * @return boolean
     */
    public function hasManager()
    {
        return $this->getManager()->count() > 0;
    }
    
    /**
     * Returns the GroupUser relation for a given user.
     * @return boolean
     */
    public function getGroupUser($user)
    {
        $userId = ($user instanceof User) ? $user->id : $user;
        return GroupUser::findOne(['user_id' => $userId, 'group_id' => $this->id]);
    }

    /**
     * Returns all GroupUser relations for this group as ActiveQuery.
     * @return ActiveQuery
     */
    public function getGroupUsers()
    {
        return $this->hasMany(GroupUser::className(), ['group_id' => 'id']);
    }

    /**
     * Returns all member user of this group as ActiveQuery
     * @return ActiveQuery
     */
    public function getUsers()
    {
        return $this->hasMany(User::className(), ['id' => 'user_id'])
                        ->via('groupUsers');
    }

    /**
     * Checks if this group has at least one user assigned.
     * @return boolean
     */
    public function hasUsers()
    {
        return $this->getUsers()->count() > 0;
    }

    /**
     * Adds a user to the group. This function will skip if the user is already
     * a member of the group.
     * @param User $user
     * @param type $isManager
     */
    public function addUser($user, $isManager = false)
    {
        if($this->getGroupUser($user) != null) {
            return;
        }
        
        $userId = ($user instanceof User) ? $user->id : $user;

        $newGroupUser = new GroupUser();
        $newGroupUser->user_id = $userId;
        $newGroupUser->group_id = $this->id;
        $newGroupUser->created_at = new \yii\db\Expression('NOW()');
        $newGroupUser->created_by = Yii::$app->user->id;
        $newGroupUser->is_group_manager = $isManager;
        $newGroupUser->save();
    }

    public function getSpace()
    {
        return $this->hasOne(Space::className(), ['id' => 'space_id']);
    }

    /**
     * Notifies groups admins for approval of new user via e-mail.
     * This should be done after a new user is created and approval is required.
     *
     * @todo Create message template, move message into translation
     */
    public static function notifyAdminsForUserApproval($user)
    {
        // No admin approval required
        if ($user->status != User::STATUS_NEED_APPROVAL || !\humhub\models\Setting::Get('needApproval', 'authentication_internal')) {
            return;
        }

        if ($user->registrationGroupId == null) {
            return;
        }

        $group = self::findOne($user->registrationGroupId);

        foreach ($group->manager as $manager) {
            $approvalUrl = \yii\helpers\Url::to(["/admin/approval"], true);

            $html = "Hello {$manager->displayName},<br><br>\n\n" .
                    "a new user {$user->displayName} needs approval.<br><br>\n\n" .
                    "Click here to validate:<br>\n\n" .
                    \yii\helpers\Html::a($approvalUrl, $approvalUrl) . "<br/> <br/>\n";

            $mail = Yii::$app->mailer->compose(['html' => '@humhub//views/mail/TextOnly'], [
                'message' => $html,
            ]);
            $mail->setFrom([\humhub\models\Setting::Get('systemEmailAddress', 'mailing') => \humhub\models\Setting::Get('systemEmailName', 'mailing')]);
            $mail->setTo($manager->email);
            $mail->setSubject(Yii::t('UserModule.models_User', "New user needs approval"));
            $mail->send();
        }
        return true;
    }

    /**
     * Returns groups which are available in user registration
     */
    public static function getRegistrationGroups()
    {
        $groups = [];

        $defaultGroup = \humhub\models\Setting::Get('defaultUserGroup', 'authentication_internal');
        if ($defaultGroup != '') {
            $group = self::findOne(['id' => $defaultGroup]);
            if ($group !== null) {
                $groups[] = $group;
                return $groups;
            }
        } else {
            $groups = self::find()->where(['show_at_registration' => '1'])->orderBy('name ASC')->all();
        }

        return $groups;
    }

    public static function getDirectoryGroups()
    {
        return self::find()->where(['show_at_directory' => '1'])->orderBy('name ASC')->all();
    }

}
