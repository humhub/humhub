<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\oauth\permissions;

use humhub\libs\BasePermission;
use Yii;

/**
 * May register OAuth clients connected to the own account.
 *
 * @since 1.20
 */
class ManageOwnOAuthClients extends BasePermission
{
    /**
     * @inheritdoc
     */
    protected $moduleId = 'oauth';

    /**
     * @inheritdoc
     */
    protected $defaultState = self::STATE_DENY;

    public function getTitle()
    {
        return Yii::t('OauthModule.base', 'Register own OAuth apps');
    }

    public function getDescription()
    {
        return Yii::t('OauthModule.base', 'Allows users to register OAuth clients that they can connect to their own account.');
    }
}
