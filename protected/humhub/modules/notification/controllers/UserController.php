<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\notification\controllers;

use humhub\modules\user\components\BaseAccountController;

/**
 * UserController allows users to modify the Notification settings.
 *
 * The page is the `NotificationSettings` island ({@see \humhub\modules\notification\widgets\SettingsPage}),
 * which saves and resets through `/api/v2/notification/settings`.
 *
 * @since 1.2
 * @author buddha
 */
class UserController extends BaseAccountController
{
    public function actionIndex()
    {
        return $this->render('notification');
    }
}
