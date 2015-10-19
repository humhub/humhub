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
     * @inheritdoc
     */
    public function rules()
    {
        return array(
            array(['allowGuestAccess', 'internalRequireApprovalAfterRegistration', 'internalAllowAnonymousRegistration'], 'boolean'),
        );
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return array(
            'allowGuestAccess' => Yii::t('InstallerModule.base', 'Allow access for not logged in users'),
            'internalRequireApprovalAfterRegistration' => Yii::t('InstallerModule.base', 'New registered users requires approval by a administrator'),
            'internalAllowAnonymousRegistration' => Yii::t('InstallerModule.base', 'Show new user registration'),
        );
    }

}
