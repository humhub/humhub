<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\admin\controllers;

use humhub\components\Module;
use humhub\modules\admin\components\Controller;
use humhub\modules\admin\models\forms\ModuleSetAsDefaultForm;
use humhub\modules\content\components\ContentContainerModule;
use humhub\modules\content\components\ContentContainerModuleManager;
use humhub\modules\space\models\Space;
use humhub\modules\user\models\User;
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
    public $adminOnly = false;

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->appendPageTitle(Yii::t('AdminModule.base', 'Modules'));
        $this->subLayout = '@admin/views/layouts/module';

        return parent::init();
    }

    /**
     * @inheritdoc
     */
    public function getAccessRules()
    {
        return [
            ['permissions' => \humhub\modules\admin\permissions\ManageModules::class]
        ];
    }

    public function actionIndex()
    {
        Yii::$app->moduleManager->flushCache();


        return $this->redirect(['/admin/module/list']);
    }


    public function actionList()
    {
        $installedModules = Yii::$app->moduleManager->getModules();

        return $this->render('list', ['installedModules' => $installedModules, 'deprecatedModuleIds' => $this->getDeprecatedModules()]);
    }

    private function getDeprecatedModules()
    {
        $deprecatedModuleIds = [];
        if (Yii::$app->hasModule('marketplace')) {
            try {
                foreach (Yii::$app->getModule('marketplace')->onlineModuleManager->getModules() as $id => $module) {
                    if (!empty($module['isDeprecated'])) {
                        $deprecatedModuleIds[] = $id;
                    }
                }

            } catch (\Exception $ex) {
            }
        }

        return $deprecatedModuleIds;
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

            $module = Yii::$app->moduleManager->getModule($moduleId);

            if ($module == null) {
                throw new HttpException(500, Yii::t('AdminModule.modules', 'Could not find requested module!'));
            }

            if (!is_writable($module->getBasePath())) {
                throw new HttpException(500, Yii::t('AdminModule.modules', 'Module path %path% is not writeable!', ['%path%' => $module->getPath()]));
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

        $model = new ModuleSetAsDefaultForm();
        $model->spaceDefaultState = ContentContainerModuleManager::getDefaultState(Space::class, $moduleId);
        $model->userDefaultState = ContentContainerModuleManager::getDefaultState(User::class, $moduleId);

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            ContentContainerModuleManager::setDefaultState(User::class, $moduleId, $model->userDefaultState);
            ContentContainerModuleManager::setDefaultState(Space::class, $moduleId, $model->spaceDefaultState);
            return $this->renderModalClose();
        }

        return $this->renderAjax('setAsDefault', ['module' => $module, 'model' => $model]);
    }


}
