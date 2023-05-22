<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\admin\controllers;

use humhub\components\Module;
use humhub\modules\admin\components\Controller;
use humhub\modules\admin\models\forms\GeneralModuleSettingsForm;
use humhub\modules\admin\models\forms\ModuleSetAsDefaultForm;
use humhub\modules\admin\permissions\ManageModules;
use humhub\modules\content\components\ContentContainerModule;
use Yii;
use yii\base\Exception;
use yii\web\HttpException;

/**
 * Module Controller controls all third party modules in a humhub installation.
 *
 * @since 0.5
 */
class ModuleController extends Controller
{

    /**
     * @inheritdoc
     */
    public $subLayout = '@admin/views/layouts/module';

    /**
     * @inheritdoc
     */
    public $adminOnly = false;

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->appendPageTitle(Yii::t('AdminModule.base', 'Modules'));

        return parent::init();
    }

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
        Yii::$app->moduleManager->flushCache();
        return $this->redirect(['/admin/module/list']);
    }

    public function actionList()
    {
        return $this->render('list');
    }

    /**
     * Enables a module
     *
     * @throws HttpException
     */
    public function actionEnable()
    {
        /** @var $module Module */
        $this->forcePostRequest();

        $moduleId = Yii::$app->request->get('moduleId');
        $module = Yii::$app->moduleManager->getModule($moduleId);

        if ($module == null) {
            throw new HttpException(500, Yii::t('AdminModule.modules', 'Could not find requested module!'));
        }

        $module->enable();

        return $this->redirect(['/admin/module/list']);
    }

    /**
     * Disables a module
     *
     * @throws HttpException
     */
    public function actionDisable()
    {

        $this->forcePostRequest();

        $moduleId = Yii::$app->request->get('moduleId');
        $module = Yii::$app->moduleManager->getModule($moduleId);

        if ($module == null) {
            throw new HttpException(500, Yii::t('AdminModule.modules', 'Could not find requested module!'));
        }

        $module->disable();

        return $this->redirect(['/admin/module/list']);
    }


    /**
     * Flushes the assets for a given module
     */
    public function actionFlush()
    {
        /** @var $module Module */
        $this->forcePostRequest();

        $moduleId = Yii::$app->request->get('moduleId');
        $module = Yii::$app->moduleManager->getModule($moduleId);
        $module->publishAssets(true);

        return $this->redirect(['/admin/module/list']);
    }

    /**
     * Removes a module
     *
     * @throws HttpException
     */
    public function actionRemove()
    {

        $this->forcePostRequest();

        $moduleId = Yii::$app->request->get('moduleId');

        if (Yii::$app->moduleManager->hasModule($moduleId) && Yii::$app->moduleManager->canRemoveModule($moduleId)) {
            /* @var Module $module */
            $module = Yii::$app->moduleManager->getModule($moduleId);

            if ($module == null) {
                throw new HttpException(500, Yii::t('AdminModule.modules', 'Could not find requested module!'));
            }

            if (!is_writable($module->getBasePath())) {
                throw new HttpException(500, Yii::t('AdminModule.modules', 'Module path %path% is not writeable!', ['%path%' => $module->getBasePath()]));
            }

            Yii::$app->moduleManager->removeModule($module->id);
        }
        return $this->redirect(['/admin/module/list']);
    }


    /**
     * Returns more information about an installed module.
     *
     * @return string
     * @throws HttpException
     */
    public function actionInfo()
    {

        $moduleId = Yii::$app->request->get('moduleId');
        try {
            $module = Yii::$app->moduleManager->getModule($moduleId);
        } catch (Exception $e) {
            throw new HttpException(404, 'Module not found!');
        }

        if ($module == null) {
            throw new HttpException(500, Yii::t('AdminModule.modules', 'Could not find requested module!'));
        }

        $readmeMd = "";
        if (file_exists($module->getBasePath() . DIRECTORY_SEPARATOR . 'README.md')) {
            $readmeMd = file_get_contents($module->getBasePath() . DIRECTORY_SEPARATOR . 'README.md');
        } elseif (file_exists($module->getBasePath() . DIRECTORY_SEPARATOR . 'docs' . DIRECTORY_SEPARATOR . 'README.md')) {
            $readmeMd = file_get_contents($module->getBasePath() . DIRECTORY_SEPARATOR . 'docs' . DIRECTORY_SEPARATOR . 'README.md');
        }

        return $this->renderAjax('info', ['name' => $module->getName(), 'description' => $module->getDescription(), 'content' => $readmeMd]);
    }

    /**
     * Sets default enabled/disabled on User or/and Space Modules
     * @throws HttpException
     */
    public function actionSetAsDefault()
    {
        $moduleId = Yii::$app->request->get('moduleId');
        $module = Yii::$app->moduleManager->getModule($moduleId);

        if ($module == null) {
            throw new HttpException(500, Yii::t('AdminModule.modules', 'Could not find requested module!'));
        }
        if (!$module instanceof ContentContainerModule) {
            throw new HttpException(500, 'Invalid module type!');
        }

        $model = (new ModuleSetAsDefaultForm())->setModule($moduleId);
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->renderModalClose();
        }

        return $this->renderAjax('setAsDefault', ['module' => $module, 'model' => $model]);
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
            return $this->redirect(['/admin/module/list']);
        }

        return $this->renderAjax('moduleSettings', [
            'settings' => $moduleSettingsForm,
        ]);
    }

}
