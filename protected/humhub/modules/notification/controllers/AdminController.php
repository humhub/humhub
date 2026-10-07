<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\notification\controllers;

use humhub\modules\admin\components\Controller;
use humhub\modules\admin\permissions\ManageSettings;

/**
 * AdminController is for system administrators to set the notification defaults.
 *
 * The page is the `NotificationSettings` island in the global scope
 * ({@see \humhub\modules\notification\widgets\SettingsPage}), which saves and resets through
 * `/api/v2/notification/settings`.
 *
 * @since 1.2
 * @author Luke
 */
class AdminController extends Controller
{
    /**
     * @inheritdoc
     */
    protected function getAccessRules()
    {
        return [
            ['permissions' => ManageSettings::class],
        ];
    }

    public function actionDefaults()
    {
        $this->subLayout = '@admin/views/layouts/setting';

        return $this->render('defaults');
    }
}
