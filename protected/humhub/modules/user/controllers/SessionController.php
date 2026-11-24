<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\controllers;

use humhub\components\access\ControllerAccess;
use humhub\components\Controller;

/**
 * Extends the session of the signed in user, for the warning before it ends (`humhub.user.session.js`, WCAG 2.2.1)
 *
 * @since 1.20
 */
class SessionController extends Controller
{
    /**
     * @inheritdoc
     */
    protected function getAccessRules()
    {
        return [
            [ControllerAccess::RULE_LOGGED_IN_ONLY],
            [ControllerAccess::RULE_POST => ['keep-alive']],
        ];
    }

    /**
     * The request itself renews the session (`yii\web\User::$authTimeout`); a request after it ended gets a 403
     */
    public function actionKeepAlive()
    {
        return $this->asJson(['success' => true]);
    }
}
