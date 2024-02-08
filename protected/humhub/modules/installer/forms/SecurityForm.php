<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\installer\forms;

use Yii;
use yii\base\Model;

/**
 * Security Settings Form
 *
 * @since 0.5
 */
class SecurityForm extends Model
{
    /**
     * @var bool allow guest acccess
     */
    public $allowGuestAccess;

    /**
     * @var bool need approval
     */
    public $internalRequireApprovalAfterRegistration;

    /**
     * @var bool allow anonymous registration
     */
    public $internalAllowAnonymousRegistration;

    /**
     * @var bool allow invite from external users by email
     */
    public $canInviteExternalUsersByEmail;

    /**
     * @var bool allow invite from external users by link
     */
    public $canInviteExternalUsersByLink;

    /**
     * @var bool enable friendship system
     */
    public $enableFriendshipModule;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['allowGuestAccess', 'internalRequireApprovalAfterRegistration', 'internalAllowAnonymousRegistration', 'enableFriendshipModule'], 'boolean'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'allowGuestAccess' => Yii::t('InstallerModule.base', 'Allow access for non-registered users to public content (guest access)'),
            'internalRequireApprovalAfterRegistration' => Yii::t('InstallerModule.base', 'Newly registered users have to be activated by an admin first'),
            'internalAllowAnonymousRegistration' => Yii::t('InstallerModule.base', 'External users can register (show registration form on login)'),
            'canInviteExternalUsersByEmail' => Yii::t('InstallerModule.base', 'Registered members can invite new users via email'),
            'canInviteExternalUsersByLink' => Yii::t('InstallerModule.base', 'Registered members can invite new users via link'),
            'enableFriendshipModule' => Yii::t('InstallerModule.base', 'Allow friendships between members'),
        ];
    }

}
