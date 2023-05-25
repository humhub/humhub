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

    /**
     * @inheritdoc
     */
    public function beforeAction($action)
    {
        if (!Module::isEnabled()) {
            throw new NotFoundHttpException(Yii::t('MarketplaceModule.base', 'Marketplace is disabled.'));
        }

        return parent::beforeAction($action);
    }

    public function actionIndex()
    {
        $this->subLayout = '@admin/views/layouts/module';
        return $this->render('index');
    }

    /**
     * Returns the thirdparty disclaimer
     */
    public function actionThirdpartyDisclaimer()
    {
        return $this->renderAjax('thirdpartyDisclaimer');
    }

    /**
     * Installs a given moduleId from marketplace
     */
    public function actionInstall()
    {
        $this->forcePostRequest();

        $moduleId = Yii::$app->request->post('moduleId');

        if (!Yii::$app->moduleManager->hasModule($moduleId)) {
            $this->module->onlineModuleManager->install($moduleId);
        }

        return $this->renderAjax('installed', ['moduleId' => $moduleId]);
    }

    /**
     * Activates a module after installation
     */
    public function actionActivate()
    {
        $this->forcePostRequest();

        $module = Yii::$app->moduleManager->getModule(Yii::$app->request->post('moduleId'));

        if ($module === null) {
            throw new NotFoundHttpException(Yii::t('MarketplaceModule.base', 'Could not find the requested module!'));
        }

        $module->enable();

        return $this->renderAjax('activated', [
            'moduleConfigUrl' => $module->getConfigUrl()
        ]);
    }

    /**
     * Module settings
     * @return string
     */
    public function actionModuleSettings()
    {
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
