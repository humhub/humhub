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
use humhub\modules\marketplace\services\ModuleService;
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
    protected function getAccessRules()
    {
        return [
            ['permissions' => ManageModules::class],
        ];
    }

    /**
     * @inheritdoc
     */
    public function beforeAction($action)
    {
        if (!Module::isMarketplaceEnabled()) {
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

        $this->getModuleService()->install();

        return $this->renderAjax('installed', [
            'moduleId' => Yii::$app->request->post('moduleId'),
        ]);
    }

    /**
     * Enables a module after installation
     *
     * @throws NotFoundHttpException
     * @see static::actionEnable()
     * @deprecated since v1.16; use static::actionEnable()
     */
    public function actionActivate(): string
    {
        return $this->actionEnable();
    }

    /**
     * Enables a module after installation
     *
     * @throws HttpException
     */
    public function actionEnable(): string
    {
        $this->forcePostRequest();

        $moduleService = $this->getModuleService();

        if (!$moduleService->enable()) {
            throw new NotFoundHttpException(Yii::t('MarketplaceModule.base', 'Could not find the requested module!'));
        }

        return $this->renderAjax('enabled', [
            'moduleConfigUrl' => $moduleService->module->getConfigUrl(),
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

    private function getModuleService(): ModuleService
    {
        return new ModuleService(Yii::$app->request->post('moduleId'));
    }

}
