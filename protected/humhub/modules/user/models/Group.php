<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\models;

use humhub\components\ActiveRecord;
use humhub\libs\ParameterEvent;
use humhub\modules\admin\notifications\ExcludeGroupNotification;
use humhub\modules\admin\notifications\IncludeGroupNotification;
use humhub\modules\admin\permissions\ManageGroups;
use humhub\modules\space\models\Space;
use humhub\modules\user\components\ActiveQueryUser;
use humhub\modules\user\Module;
use Throwable;
use Yii;
use yii\base\InvalidConfigException;
use yii\db\ActiveQuery;
use yii\db\Expression;
use yii\db\StaleObjectException;
use yii\helpers\Html;
use yii\helpers\Url;

/**
 * This is the model class for table "group".
 *
 * @property int $id
 * @property int $space_id
 * @property string $name
 * @property string $description
 * @property string $created_at
 * @property int $created_by
 * @property int $sort_order
 * @property int $show_at_directory
 * @property int $show_at_registration
 * @property string $updated_at
 * @property int $updated_by
 * @property int $is_admin_group
 * @property int $is_default_group
 * @property int $is_protected
 * @property int $notify_users
 *
 * @property User[] $manager
 * @property Space|null $defaultSpace
 * @property GroupUser[] groupUsers
 * @property GroupSpace[] groupSpaces
 */
class Group extends ActiveRecord
{
    public const EVENT_GET_REGISTRATION_GROUPS = 'getRegistrationGroups';
    public const SCENARIO_EDIT = 'edit';

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
            [['sort_order', 'notify_users', 'is_default_group', 'is_protected'], 'integer'],
            [['description'], 'string'],
            [['name'], 'string', 'max' => 120],
            ['show_at_registration', 'validateShowAtRegistration'],
            ['is_default_group', 'validateIsDefaultGroup'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function beforeDelete()
    {
        GroupSpace::deleteAll(['group_id' => $this->id]);

        return parent::beforeDelete();
    }

    public function validateShowAtRegistration($attribute, $params)
    {
        if ($this->is_admin_group && $this->show_at_registration) {
            $this->addError($attribute, 'Admin group can\'t be a registration group!');
        }
    }

    /**
     * Validate default group
     * @param string $attribute
     */
    public function validateIsDefaultGroup($attribute)
    {
        if ($this->is_admin_group && $this->is_default_group) {
            $this->addError($attribute, 'Admin group can\'t be a default group!');
        }

        if ($this->getOldAttribute($attribute) && !$this->is_default_group) {
            $this->addError($attribute, 'One group must be a default!');
        }
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => Yii::t('UserModule.base', 'Name'),
            'defaultSpaceGuid' => Yii::t('UserModule.base', 'Default Space'),
            'managerGuids' => Yii::t('UserModule.base', 'Group Manager(s)'),
            'description' => Yii::t('UserModule.base', 'Description'),
            'created_at' => Yii::t('UserModule.base', 'Created at'),
            'created_by' => Yii::t('UserModule.base', 'Created by'),
            'updated_at' => Yii::t('UserModule.base', 'Updated at'),
            'updated_by' => Yii::t('UserModule.base', 'Updated by'),
            'show_at_registration' => Yii::t('UserModule.base', 'Show At Registration'),
            'show_at_directory' => Yii::t('UserModule.base', 'Visible'),
            'sort_order' => Yii::t('UserModule.base', 'Sort order'),
            'notify_users' => Yii::t('UserModule.base', 'Enable Notifications'),
            'is_default_group' => Yii::t('UserModule.base', 'Default Group'),
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeHints()
    {
        return [
            'managerGuids' => Yii::t('UserModule.base', 'The Group Manager can approve pending registrations of this group.'),
            'notify_users' => Yii::t('AdminModule.user', 'Send notifications to users when added to or removed from the group.'),
            'show_at_registration' => Yii::t('AdminModule.user', 'Make the group selectable at registration.'),
            'show_at_directory' => Yii::t('AdminModule.user', 'Will be used as a filter in \'People\'.'),
            'is_default_group' => Yii::t('AdminModule.user', 'Applied to new or existing users without any other group membership.'),
        ];
    }

    /**
     * @return null|Space[]
     * @since 1.8
     */
    public function getDefaultSpaces()
    {
        return Space::find()
            ->innerJoin('group_space', 'space.id = group_space.space_id')
            ->where(['group_space.group_id' => $this->id])
            ->all();
    }

    public function beforeSave($insert)
    {
        if (empty($this->sort_order)) {
            $this->sort_order = 100;
        }

        if ($this->getOldAttribute('is_default_group') && !$this->is_default_group) {
            $this->is_default_group = 1;
            return false;
        }

        if ($this->show_at_registration && $this->is_admin_group) {
            // Admin group cannot be shown at registration
            $this->show_at_registration = 0;
        }

        if ($this->is_default_group && $this->is_admin_group) {
            // Admin group cannot be default
            $this->is_default_group = 0;
        }

        return parent::beforeSave($insert);
    }

    /**
     * @inheritdoc
     */
    public function afterSave($insert, $changedAttributes)
    {
        if ($this->is_default_group) {
            // Only single group can be default:
            Group::updateAll(['is_default_group' => '0'], ['!=', 'id', $this->id]);
        }

        parent::afterSave($insert, $changedAttributes);


    }

    /**
     * @inheritdoc
     */
    public function afterDelete()
    {
        /* @var $module Module */
        $module = Yii::$app->getModule('user');
        if ($defaultGroup = $module->getDefaultGroup()) {
            $defaultGroup->assignDefaultGroup();
        }

        parent::afterDelete();
    }

    /**
     * Assign users to this Default Group who were not assigned to any other group before
     */
    public function assignDefaultGroup()
    {
        if (empty($this->id) || !$this->is_default_group || $this->is_admin_group) {
            return;
        }

        Yii::$app->getDb()->createCommand('INSERT INTO group_user (user_id, group_id, created_at, updated_at)
            SELECT user.id, :defaultGroupId, NOW(), NOW()
              FROM user
              LEFT JOIN group_user ON group_user.user_id = user.id
             WHERE group_user.id IS NULL
               AND user.status != :userStatusSoftDeleted', [
            ':defaultGroupId' => $this->id,
            ':userStatusSoftDeleted' => User::STATUS_SOFT_DELETED,
        ])->execute();
    }

    /**
     * Returns the admin group.
     * @return Group
     */
    public static function getAdminGroup()
    {
        return self::findOne(['is_admin_group' => '1']);
    }

    public static function getAdminGroupId(): int
    {
        $adminGroupId = Yii::$app->getModule('user')->settings->get('group.adminGroupId');
        if ($adminGroupId === null) {
            $adminGroupId = self::getAdminGroup()->id;
            Yii::$app->getModule('user')->settings->set('group.adminGroupId', $adminGroupId);
        }
        return $adminGroupId;
    }

    /**
     * Returns all user which are defined as manager in this group as ActiveQuery.
     * @return ActiveQuery
     */
    public function getManager()
    {
        return $this->hasMany(User::class, ['id' => 'user_id'])
            ->via('groupUsers', function ($query) {
                $query->where(['is_group_manager' => '1']);
            });
    }

    /**
     * Checks if this group has at least one Manager assigned.
     * @return bool
     */
    public function hasManager()
    {
        return $this->getManager()->count() > 0;
    }

    /**
     * Returns the GroupUser relation for a given user.
     * @param User|string $user
     *
     * @return GroupUser|null
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
        return $this->hasMany(GroupUser::class, ['group_id' => 'id']);
    }

    /**
     * Returns all member user of this group as ActiveQuery
     *
     * @return ActiveQueryUser
     */
    public function getUsers()
    {
        $query = User::find();
        $query->leftJoin('group_user', 'group_user.user_id=user.id AND group_user.group_id=:groupId', [
            ':groupId' => $this->id,
        ]);
        $query->andWhere(['IS NOT', 'group_user.id', new Expression('NULL')]);
        $query->multiple = true;

        return $query;
    }

    /**
     * Checks if this group has at least one user assigned.
     * @return bool
     */
    public function hasUsers()
    {
        return $this->getUsers()->count() > 0;
    }

    /**
     * @param $user
     * @return bool
     */
    public function isManager($user)
    {
        $userId = ($user instanceof User) ? $user->id : $user;
        return $this->getGroupUsers()->where(['user_id' => $userId, 'is_group_manager' => true])->count() > 0;
    }

    /**
     * @param $user
     * @return bool
     */
    public function isMember($user)
    {
        return $this->getGroupUser($user) != null;
    }

    /**
     * Adds a user to the group. This function will skip if the user is already a member of the group.
     *
     * @param User $user user id or user model
     * @param bool $isManager mark as group manager
     * @return bool true - on success adding user, false - if already member or cannot be added by some reason
     * @throws InvalidConfigException
     */
    public function addUser($user, $isManager = false)
    {
        if ($this->isMember($user)) {
            return false;
        }

        $userId = ($user instanceof User) ? $user->id : $user;

        $newGroupUser = new GroupUser();
        $newGroupUser->user_id = $userId;
        $newGroupUser->group_id = $this->id;
        $newGroupUser->created_at = date('Y-m-d H:i:s');
        $newGroupUser->created_by = Yii::$app->user->id;
        $newGroupUser->is_group_manager = $isManager;
        if ($newGroupUser->save()) {
            if ($this->notify_users && !Yii::$app->user->isGuest) {
                if (!($user instanceof User)) {
                    $user = User::findOne(['id' => $user]);
                }
                IncludeGroupNotification::instance()
                    ->about($this)
                    ->from(Yii::$app->user->identity)
                    ->send($user);
            }
            return true;
        }

        return false;
    }

    /**
     * Removes a user from the group.
     * @param User|string $user userId or user model
     * @return bool
     * @throws Throwable
     * @throws StaleObjectException
     */
    public function removeUser($user)
    {
        $groupUser = $this->getGroupUser($user);
        if (!$groupUser) {
            return false;
        }

        if ($groupUser->delete()) {
            if ($this->notify_users) {
                if (!($user instanceof User)) {
                    $user = User::findOne(['id' => $user]);
                }
                ExcludeGroupNotification::instance()
                    ->about($this)
                    ->from(Yii::$app->user->identity)
                    ->send($user);
            }
            return true;
        }

        return false;
    }

    /**
     * Notifies groups admins for approval of new user via e-mail.
     * This should be done after a new user is created and approval is required.
     *
     * @param User $user
     * @return true|void
     * @todo Create message template, move message into translation
     */
    public static function notifyAdminsForUserApproval($user)
    {
        // No admin approval required
        if ($user->status != User::STATUS_NEED_APPROVAL
            || !Yii::$app->getModule('user')->settings->get('auth.needApproval', 'user')) {
            return;
        }

        if ($user->registrationGroupId == null) {
            return;
        }

        $group = self::findOne($user->registrationGroupId);
        $approvalUrl = Url::to(["/admin/approval"], true);

        foreach ($group->manager as $manager) {

            Yii::$app->i18n->setUserLocale($manager);

            $html = Yii::t(
                'UserModule.auth',
                'Hello {displayName},',
                ['displayName' => $manager->displayName],
            ) . "<br><br>\n\n"
                . Yii::t(
                    'UserModule.auth',
                    'a new user {displayName} needs approval.',
                    ['displayName' => $user->displayName],
                ) . "<br><br>\n\n"
                . Yii::t('UserModule.auth', 'Please click on the link below to view request:')
                . "<br>\n\n"
                . Html::a($approvalUrl, $approvalUrl) . "<br/> <br/>\n";

            $mail = Yii::$app->mailer->compose(['html' => '@humhub/views/mail/TextOnly'], [
                'message' => $html,
            ]);

            $mail->setTo($manager->email);
            $mail->setSubject(Yii::t('UserModule.auth', "New user needs approval"));
            $mail->send();
        }

        Yii::$app->i18n->autosetLocale();

        return true;
    }

    /**
     * Returns groups which are available in user registration
     *
     * @param User|null $user
     * @return Group[] the groups which can be selected in registration
     */
    public static function getRegistrationGroups(?User $user = null)
    {
        if (Yii::$app->getModule('user')->settings->get('auth.showRegistrationUserGroup')) {
            $groups = self::find()
                ->where(['show_at_registration' => 1, 'is_admin_group' => 0])
                ->orderBy('name ASC')
                ->all();
        }

        if (empty($groups)) {
            $groups = [];
            if ($defaultGroup = Yii::$app->getModule('user')->getDefaultGroup()) {
                $groups[] = $defaultGroup;
            }
        }

        $evt = new ParameterEvent([
            'user' => $user,
            'groups' => $groups,
        ]);
        ParameterEvent::trigger(static::class, static::EVENT_GET_REGISTRATION_GROUPS, $evt);

        return $evt->parameters['groups'];
    }

    /**
     * @return array|\yii\db\ActiveRecord[]
     */
    public static function getDirectoryGroups()
    {
        return self::find()->where(['show_at_directory' => '1'])->orderBy([
            'sort_order' => SORT_ASC,
            'name' => SORT_ASC,
        ])->all();
    }

    /**
     * Returns all GroupSpace relations for this group as ActiveQuery.
     * @return ActiveQuery
     * @since 1.8
     */
    public function getGroupSpaces()
    {
        return $this->hasMany(GroupSpace::class, ['group_id' => 'id']);
    }


    /**
     * Check if this Group can be deleted by current User
     *
     * @return bool
     * @since 1.9
     */
    public function canDelete()
    {
        return Yii::$app->user->can(ManageGroups::class) && !(
            $this->isNewRecord
                || $this->is_admin_group
                || $this->is_default_group
                || $this->is_protected
        );
    }
}
