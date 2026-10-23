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
 * May create personal access tokens (when the administrator allows tokens at all).
 *
 * @since 1.20
 */
class CreatePersonalAccessToken extends BasePermission
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
        return Yii::t('OauthModule.base', 'Create personal access tokens');
    }

    public function getDescription()
    {
        return Yii::t('OauthModule.base', 'Allows users to create tokens for scripts and agents that access their account via the API.');
    }
}
