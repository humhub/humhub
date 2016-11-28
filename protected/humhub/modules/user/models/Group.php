<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2016 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\models;

use Yii;
use humhub\components\ActiveRecord;
use humhub\modules\space\models\Space;


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
class Group extends ActiveRecord
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
            [['name'], 'required', 'on' => self::SCENARIO_EDIT],
            [['space_id'], 'integer'],
            [['description', 'managerGuids', 'defaultSpaceGuid'], 'string'],
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
            'space_id' => Yii::t('UserModule.models_User', 'Space ID'),
            'name' => Yii::t('UserModule.models_User', 'Name'),
            'defaultSpaceGuid' => Yii::t('UserModule.models_User', 'Default Space'),
            'managerGuids' => Yii::t('UserModule.models_User', 'Manager'),
            'description' => Yii::t('UserModule.models_User', 'Description'),
            'created_at' => Yii::t('UserModule.models_User', 'Created at'),
            'created_by' => Yii::t('UserModule.models_User', 'Created by'),
            'updated_at' => Yii::t('UserModule.models_User', 'Updated at'),
            'updated_by' => Yii::t('UserModule.models_User', 'Updated by'),
            'show_at_registration' => Yii::t('UserModule.models_User', 'Show At Registration'),
            'show_at_directory' => Yii::t('UserModule.models_User', 'Show At Directory'),
        ];
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
     *
     * @return ActiveQuery
     */
    public function getUsers()
    {
        $query = User::find();
        $query->leftJoin('group_user', 'group_user.user_id=user.id AND group_user.group_id=:groupId', [
            ':groupId' => $this->id
        ]);
        $query->andWhere(['IS NOT', 'group_user.id', new \yii\db\Expression('NULL')]);
        $query->multiple = true;
        return $query;
    }

    /**
     * Checks if this group has at least one user assigned.
     * @return boolean
     */
    public function hasUsers()
    {
        return $this->getUsers()->count() > 0;
    }

    public function isManager($user)
    {
        $userId = ($user instanceof User) ? $user->id : $user;
        return $this->getGroupUsers()->where(['user_id' => $userId, 'is_group_manager' => true])->count() > 0;
    }

    public function isMember($user)
    {
        return $this->getGroupUser($user) != null;
    }

    /**
     * Adds a user to the group. This function will skip if the user is already
     * a member of the group.
     * @param User $user user id or user model
     * @param type $isManager
     */
    public function addUser($user, $isManager = false)
    {
        if ($this->isMember($user)) {
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

    /**
     * Removes a user from the group.
     * @param type $user userId or user model
     */
    public function removeUser($user)
    {
        $groupUser = $this->getGroupUser($user);
        if ($groupUser != null) {
            $groupUser->delete();
        }
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
        if ($user->status != User::STATUS_NEED_APPROVAL || !Yii::$app->getModule('user')->settings->get('auth.needApproval', 'user')) {
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
            $mail->setFrom([Yii::$app->settings->get('mailer.systemEmailAddress') => Yii::$app->settings->get('mailer.systemEmailName')]);
            $mail->setTo($manager->email);
            $mail->setSubject(Yii::t('UserModule.models_User', "New user needs approval"));
            $mail->send();
        }
        return true;
    }

    /**
     * Returns groups which are available in user registration
     * 
     * @return Group[] the groups which can be selected in registration
     */
    public static function getRegistrationGroups()
    {
        $groups = [];

        $defaultGroup = Yii::$app->getModule('user')->settings->get('auth.defaultUserGroup');
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
