<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2019 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\marketplace\controllers;

use humhub\modules\admin\components\Controller;
use humhub\modules\admin\permissions\ManageModules;
use humhub\modules\marketplace\models\forms\GeneralModuleSettingsForm;
use humhub\modules\marketplace\Module;
use Yii;
use yii\web\HttpException;
use yii\web\NotFoundHttpException;

/**
 * Class BrowseController
 *
 * @property Module $module
 * @package humhub\modules\marketplace\controllers
 */
class BrowseController extends Controller
{
    /**
     * @inheritdoc
     */
    public function getAccessRules()
    {
        return [
            ['permissions' => ManageModules::class]
        ];
    }

    public function actionIndex()
    {
        if (!Module::isEnabled()) {
            throw new NotFoundHttpException();
        }

        $this->subLayout = '@admin/views/layouts/module';
        return $this->render('index');
    }

    /**
     * Returns the thirdparty disclaimer
     *
     * @throws HttpException
     */
    public function actionThirdpartyDisclaimer()
    {
        return $this->renderAjax('thirdpartyDisclaimer', []);
    }

    /**
     * Installs a given moduleId from marketplace
     */
    public function actionInstall()
    {
        $this->forcePostRequest();

        $moduleId = Yii::$app->request->get('moduleId');

        if (!Yii::$app->moduleManager->hasModule($moduleId)) {
            $this->module->onlineModuleManager->install($moduleId);
        }

        return $this->redirect(['/admin/module/list']);
    }

    /**
     * Module settings
     * @return string
     */
    public function actionModuleSettings()
    {
        if (!Module::isEnabled()) {
            throw new NotFoundHttpException();
        }

        $moduleSettingsForm = new GeneralModuleSettingsForm();

        if ($moduleSettingsForm->load(Yii::$app->request->post()) && $moduleSettingsForm->save()) {
            $this->view->saved();
            return $this->redirect(['/marketplace/browse']);
        }

        return $this->renderAjax('moduleSettings', [
            'settings' => $moduleSettingsForm,
        ]);
    }

}
