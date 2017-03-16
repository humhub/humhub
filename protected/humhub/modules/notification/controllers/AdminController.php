<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\notification\controllers;

use Yii;
use humhub\modules\admin\components\Controller;
use humhub\modules\notification\models\forms\NotificationSettings;

/**
 * AdminController is for system administrators to set activity e-mail defaults.
 *
 * @since 1.2
 * @author Luke
 */
class AdminController extends Controller
{
    /**
     * @inheritdoc
     */
    public function getAccessRules()
    {
        return [
            ['permissions' => \humhub\modules\admin\permissions\ManageSettings::className()]
        ];
    }

    public function actionDefaults()
    {
        $this->subLayout = '@admin/views/layouts/setting';

        $form = new NotificationSettings();
        if ($form->load(Yii::$app->request->post()) && $form->save()) {
            $this->view->saved();
        }

        return $this->render('defaults', ['model' => $form]);
    }
}