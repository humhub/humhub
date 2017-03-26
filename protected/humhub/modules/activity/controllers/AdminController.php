<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\activity\controllers;

use Yii;
use humhub\modules\admin\components\Controller;
use humhub\modules\activity\models\MailSummaryForm;

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
        $model = new MailSummaryForm();

        $model->loadCurrent();
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            $this->view->saved();
        }

        return $this->render('defaults', [
            'model' => $model
        ]);
    }

}
