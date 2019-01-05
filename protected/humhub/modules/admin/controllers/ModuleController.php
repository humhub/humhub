<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\admin\controllers;

use humhub\components\Module;
use humhub\modules\admin\components\Controller;
use humhub\modules\admin\libs\OnlineModuleManager;
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
    private $_onlineModuleManager = null;

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

        return $this->render('list', ['installedModules' => $installedModules]);
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
            throw new HttpException(500, Yii::t('AdminModule.controllers_ModuleController', 'Could not find requested module!'));
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
            throw new HttpException(500, Yii::t('AdminModule.controllers_ModuleController', 'Could not find requested module!'));
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
     * Installs a given moduleId from marketplace
     */
    public function actionInstall()
    {

        $this->forcePostRequest();

        $moduleId = Yii::$app->request->get('moduleId');

        if (!Yii::$app->moduleManager->hasModule($moduleId)) {
            $onlineModules = new OnlineModuleManager();
            $onlineModules->install($moduleId);
        }

        // Redirect to Module Install?
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
                throw new HttpException(500, Yii::t('AdminModule.controllers_ModuleController', 'Could not find requested module!'));
            }

            if (!is_writable($module->getBasePath())) {
                throw new HttpException(500, Yii::t('AdminModule.controllers_ModuleController', 'Module path %path% is not writeable!', ['%path%' => $module->getPath()]));
            }

            Yii::$app->moduleManager->removeModule($module->id);
        }
        return $this->redirect(['/admin/module/list']);
    }

    /**
     * Updates a module with the most recent version online
     *
     * @throws HttpException
     */
    public function actionUpdate()
    {

        $this->forcePostRequest();

        $moduleId = Yii::$app->request->get('moduleId');

        /** @var Module $module */
        $module = Yii::$app->moduleManager->getModule($moduleId);

        if ($module == null) {
            throw new HttpException(500, Yii::t('AdminModule.controllers_ModuleController', 'Could not find requested module!'));
        }

        $onlineModules = $this->getOnlineModuleManager();
        $onlineModules->update($moduleId);

        try {
            $module->publishAssets(true);
        } catch (\Exception $e) {
            Yii::error($e);
        }

        return $this->redirect(['/admin/module/list']);
    }

    /**
     * Complete list of all modules
     */
    public function actionListOnline()
    {
        $keyword = Yii::$app->request->post('keyword', "");

        $onlineModules = $this->getOnlineModuleManager();
        $modules = $onlineModules->getModules();

        if ($keyword != "") {
            $results = [];
            foreach ($modules as $module) {
                if (stripos($module['name'], $keyword) !== false || stripos($module['description'], $keyword) !== false) {
                    $results[] = $module;
                }
            }
            $modules = $results;
        }

        return $this->render('listOnline', ['modules' => $modules, 'keyword' => $keyword]);
    }

    /**
     * Lists all available module updates
     */
    public function actionListUpdates()
    {
        $onlineModules = $this->getOnlineModuleManager();
        $modules = $onlineModules->getModuleUpdates();

        return $this->render('listUpdates', ['modules' => $modules]);
    }

    /**
     * Complete list of all modules
     */
    public function actionListPurchases()
    {
        $hasError = false;
        $message = "";

        $licenceKey = Yii::$app->request->post('licenceKey', "");
        if ($licenceKey != "") {
            $result = \humhub\modules\admin\libs\HumHubAPI::request('v1/modules/registerPaid', ['licenceKey' => $licenceKey]);
            if (!isset($result['status'])) {
                $hasError = true;
                $message = 'Could not connect to HumHub API!';
            } elseif ($result['status'] == 'ok' || $result['status'] == 'created') {
                $message = 'Module licence added!';
                $licenceKey = "";
            } else {
                $hasError = true;
                $message = 'Invalid module licence key!';
            }
        }

        // Only showed purchased modules
        $onlineModules = $this->getOnlineModuleManager();
        $modules = $onlineModules->getModules(false);


        foreach ($modules as $i => $module) {
            if (!isset($module['purchased']) || !$module['purchased']) {
                unset($modules[$i]);
            }
        }

        return $this->render('listPurchases', ['modules' => $modules, 'licenceKey' => $licenceKey, 'hasError' => $hasError, 'message' => $message]);
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
            throw new HttpException(500, Yii::t('AdminModule.controllers_ModuleController', 'Could not find requested module!'));
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
     * Returns the thirdparty disclaimer
     *
     * @throws HttpException
     */
    public function actionThirdpartyDisclaimer()
    {
        return $this->renderAjax('thirdpartyDisclaimer', []);
    }

    /**
     * Sets default enabled/disabled on User or/and Space Modules
     *
     * @throws CHttpException
     */
    public function actionSetAsDefault()
    {
        $moduleId = Yii::$app->request->get('moduleId');
        $module = Yii::$app->moduleManager->getModule($moduleId);

        if ($module == null) {
            throw new HttpException(500, Yii::t('AdminModule.controllers_ModuleController', 'Could not find requested module!'));
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

    public function getOnlineModuleManager()
    {

        if ($this->_onlineModuleManager === null) {
            $this->_onlineModuleManager = new OnlineModuleManager();
        }

        return $this->_onlineModuleManager;
    }

}
