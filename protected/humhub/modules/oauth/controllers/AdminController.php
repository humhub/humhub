<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\oauth\controllers;

use humhub\modules\admin\components\Controller;
use humhub\modules\admin\permissions\ManageSettings;
use humhub\modules\oauth\models\forms\SettingsForm;
use Yii;

/**
 * "Apps & API Access" in the user settings of the administration.
 *
 * @since 1.20
 */
class AdminController extends Controller
{
    /**
     * @inheritdoc
     */
    protected function getAccessRules()
    {
        return [['permissions' => [ManageSettings::class]]];
    }

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->subLayout = '@admin/views/layouts/user';
        parent::init();
    }

    public function actionIndex()
    {
        $model = new SettingsForm();
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            $this->view->saved();
            return $this->redirect(['index']);
        }

        return $this->render('index', ['model' => $model]);
    }
}
