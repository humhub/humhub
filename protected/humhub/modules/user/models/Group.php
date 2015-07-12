<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\models;

use Yii;
use humhub\modules\space\models\Space;
use humhub\modules\user\models\User;
use humhub\models\Setting;

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
 * @property string $ldap_dn
 * @property integer $can_create_public_spaces
 * @property integer $can_create_private_spaces
 */
class Group extends \yii\db\ActiveRecord
{

    public $adminGuids;
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
            [['space_id', 'created_by', 'updated_by', 'can_create_public_spaces', 'can_create_private_spaces'], 'integer'],
            [['description', 'adminGuids', 'defaultSpaceGuid'], 'string'],
            [['created_at', 'updated_at'], 'safe'],
            [['name'], 'string', 'max' => 45],
            [['ldap_dn'], 'string', 'max' => 255]
        ];
    }

    public function scenarios()
    {
        $scenarios = parent::scenarios();
        $scenarios['edit'] = ['name', 'description', 'ldap_dn', 'can_create_public_spaces', 'can_create_private_spaces', 'adminGuids', 'defaultSpaceGuid'];
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
            'description' => 'Description',
            'created_at' => 'Created At',
            'created_by' => 'Created By',
            'updated_at' => 'Updated At',
            'updated_by' => 'Updated By',
            'ldap_dn' => 'Ldap Dn',
            'can_create_public_spaces' => 'Can Create Public Spaces',
            'can_create_private_spaces' => 'Can Create Private Spaces',
        ];
    }

    public function beforeSave($insert)
    {

        // When on edit form scenario, save also defaultSpaceGuid/adminGuids
        if ($this->scenario == 'edit') {
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
        if ($this->scenario == 'edit') {
            \humhub\modules\user\models\GroupAdmin::deleteAll(['group_id' => $this->id]);
            $adminUsers = array();
            foreach (explode(",", $this->adminGuids) as $adminGuid) {

                // Ensure guids valid characters
                $adminGuid = preg_replace("/[^A-Za-z0-9\-]/", '', $adminGuid);

                // Try load user
                $user = \humhub\modules\user\models\User::findOne(['guid' => $adminGuid]);
                if ($user != null) {
                    $groupAdmin = new GroupAdmin;
                    $groupAdmin->user_id = $user->id;
                    $groupAdmin->group_id = $this->id;
                    $groupAdmin->save();
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

    public function populateAdminGuids()
    {
        $this->adminGuids = "";
        foreach ($this->admins as $admin) {
            $this->adminGuids .= $admin->user->guid . ",";
        }
    }

    public function getAdmins()
    {
        return $this->hasMany(GroupAdmin::className(), ['group_id' => 'id']);
    }

    public function getUsers()
    {
        return $this->hasMany(User::className(), ['user_id' => 'id']);
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
    public function notifyAdminsForUserApproval($user)
    {
        // No admin approval required
        if ($user->status != User::STATUS_NEED_APPROVAL || !Setting::Get('needApproval', 'authentication_internal')) {
            return;
        }

        foreach ($this->admins as $admin) {
            if ($admin->user !== null) {
                /*
                  $approvalUrl = Url::to(["/admin/approval"], true);

                  $html = "Hello {$adminUser->displayName},<br><br>\n\n" .
                  "a new user {$this->displayName} needs approval.<br><br>\n\n" .
                  "Click here to validate:<br>\n\n" .
                  HHtml::link($approvalUrl, $approvalUrl) . "<br/> <br/>\n";

                  $message = new HMailMessage();
                  $message->addFrom(Setting::Get('systemEmailAddress', 'mailing'), Setting::Get('systemEmailName', 'mailing'));
                  $message->addTo($adminUser->email);
                  $message->view = "application.views.mail.TextOnly";
                  $message->subject = Yii::t('UserModule.models_User', "New user needs approval");
                  $message->setBody(array('message' => $html), 'text/html');
                  Yii::app()->mail->send($message);
                 * 
                 */
            } else {
                Yii::warning("Could not load Group Admin User. Inconsistent Group Admin Record! User Id: " . $admin->user_id);
            }
        }

        return true;
    }

}
