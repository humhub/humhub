<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\oauth\components;

use Yii;
use yii\web\ForbiddenHttpException;

/**
 * Shared by the controllers handing out credentials (client secrets, tokens).
 *
 * @since 1.20
 */
trait CredentialActions
{
    /**
     * An impersonating administrator must not create credentials that outlive the impersonation.
     */
    protected function denyWhileImpersonating(): void
    {
        if (Yii::$app->user->getImpersonation()->isActive()) {
            throw new ForbiddenHttpException(Yii::t('OauthModule.base', 'Credentials cannot be created while impersonating a user.'));
        }
    }
}
