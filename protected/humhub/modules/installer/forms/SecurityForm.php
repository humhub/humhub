<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\installer\forms;

use Yii;

/**
 * Security Settings Form
 *
 * @since 0.5
 */
class SecurityForm extends \yii\base\Model
{

    /**
     * @var boolean allow guest acccess
     */
    public $allowGuestAccess;

    /**
     * @var boolean need approval
     */
    public $internalRequireApprovalAfterRegistration;

    /**
     * @var boolean allow anonymous registration
     */
    public $internalAllowAnonymousRegistration;

    /**
     * @var boolean allow invite from external users by email
     */
    public $canInviteExternalUsersByEmail;

    /**
     * @var boolean enable friendship system
     */
    public $enableFriendshipModule;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return array(
            array(['allowGuestAccess', 'internalRequireApprovalAfterRegistration', 'internalAllowAnonymousRegistration', 'enableFriendshipModule'], 'boolean'),
        );
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return array(
            'allowGuestAccess' => Yii::t('InstallerModule.forms_SecurityForm', 'Allow access for non-registered users to public content (guest access)'),
            'internalRequireApprovalAfterRegistration' => Yii::t('InstallerModule.forms_SecurityForm', 'Newly registered users have to be activated by an admin first'),
            'internalAllowAnonymousRegistration' => Yii::t('InstallerModule.forms_SecurityForm', 'External users can register (show registration form on login)'),
            'canInviteExternalUsersByEmail' => Yii::t('InstallerModule.forms_SecurityForm', 'Registered members can invite new users via email'),
            'enableFriendshipModule' => Yii::t('InstallerModule.forms_SecurityForm', 'Allow friendships between members'),
        );
    }

}
