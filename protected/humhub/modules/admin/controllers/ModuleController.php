<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\admin\controllers;

use humhub\components\Module;
use humhub\modules\admin\components\Controller;
use humhub\modules\admin\jobs\DisableModuleJob;
use humhub\modules\admin\jobs\RemoveModuleJob;
use humhub\modules\admin\models\forms\ModuleSetAsDefaultForm;
use humhub\modules\admin\permissions\ManageModules;
use humhub\modules\admin\permissions\ManageSettings;
use humhub\modules\content\components\ContentContainerModule;
use humhub\modules\queue\helpers\QueueHelper;
use Yii;
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
    public $adminOnly = false;

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->appendPageTitle(Yii::t('AdminModule.base', 'Modules'));

        parent::init();
    }

    /**
     * @inheritdoc
     */
    protected function getAccessRules()
    {
        return [
            ['permissions' => [ManageModules::class]],
            ['permissions' => [ManageSettings::class], 'actions' => ['index', 'list']],
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

        if (QueueHelper::isQueued(new DisableModuleJob(['moduleId' => $moduleId]))) {
            $this->view->error(Yii::t('AdminModule.modules', 'Deactivation of this module has not been completed yet. Please retry in a few minutes.'));
        } elseif (QueueHelper::isQueued(new RemoveModuleJob(['moduleId' => $moduleId]))) {
            $this->view->error(Yii::t('AdminModule.modules', 'Uninstallation of this module has not been completed yet. It will be removed in a few minutes.'));
        } else {
            $module->enable();
        }

        return $this->redirectToModules();
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

        Yii::$app->queue->push(new DisableModuleJob(['moduleId' => $moduleId]));
        $this->view->info(Yii::t('AdminModule.modules', 'Module deactivation in progress. This process may take a moment.'));

        return $this->redirectToModules();
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

        return $this->redirectToModules();
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

            if (!is_writable(realpath($module->getBasePath()))) {
                throw new HttpException(500, Yii::t('AdminModule.modules', 'Module path %path% is not writeable!', ['%path%' => $module->getBasePath()]));
            }

            Yii::$app->queue->push(new RemoveModuleJob(['moduleId' => $moduleId]));
            $this->view->info(Yii::t('AdminModule.modules', 'Module uninstall in progress. This process may take a moment.'));
        }

        return $this->redirectToModules();
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

    private function redirectToModules()
    {
        return $this->redirect(['/admin/module/list']);
    }

}
