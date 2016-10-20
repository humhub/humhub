<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2016 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\admin\controllers;

use Yii;

use yii\web\HttpException;
use humhub\modules\admin\components\Controller;
use humhub\modules\admin\libs\OnlineModuleManager;
use humhub\modules\content\components\ContentContainerModule;
use humhub\modules\user\models\User;
use humhub\modules\space\models\Space;

/**
 * Module Controller controls all third party modules in a humhub installation.
 *
 * @since 0.5
 */
class ModuleController extends Controller
{

    private $_onlineModuleManager = null;

    public function init() {
        $this->appendPageTitle(Yii::t('AdminModule.base', 'Modules'));
        return parent::init();
    }
    
    public function actionIndex()
    {
        Yii::$app->moduleManager->flushCache();
        return $this->redirect(['/admin/module/list']);
    }

    public function actionList()
    {
        $installedModules = Yii::$app->moduleManager->getModules();
        return $this->render('list', array('installedModules' => $installedModules));
    }

    /**
     * Enables a module
     *
     * @throws HttpException
     */
    public function actionEnable()
    {
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
     * @throws CHttpException
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
                throw new HttpException(500, Yii::t('AdminModule.controllers_ModuleController', 'Module path %path% is not writeable!', array('%path%' => $module->getPath())));
            }

            Yii::$app->moduleManager->removeModule($module->id);
        }
        return $this->redirect(['/admin/module/list']);
    }

    /**
     * Updates a module with the most recent version online
     *
     * @throws CHttpException
     */
    public function actionUpdate()
    {

        $this->forcePostRequest();

        $moduleId = Yii::$app->request->get('moduleId');
        $module = Yii::$app->moduleManager->getModule($moduleId);

        if ($module == null) {
            throw new HttpException(500, Yii::t('AdminModule.controllers_ModuleController', 'Could not find requested module!'));
        }

        $onlineModules = $this->getOnlineModuleManager();
        $onlineModules->update($moduleId);

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
            $results = array();
            foreach ($modules as $module) {
                if (stripos($module['name'], $keyword) !== false || stripos($module['description'], $keyword) !== false) {
                    $results[] = $module;
                }
            }
            $modules = $results;
        }

        return $this->render('listOnline', array('modules' => $modules, 'keyword' => $keyword));
    }

    /**
     * Lists all available module updates
     */
    public function actionListUpdates()
    {
        $onlineModules = $this->getOnlineModuleManager();
        $modules = $onlineModules->getModuleUpdates();

        return $this->render('listUpdates', array('modules' => $modules));
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
            } elseif ($result['status'] == 'ok' || $result['status'] == 'created' ) {
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

        return $this->render('listPurchases', array('modules' => $modules, 'licenceKey' => $licenceKey, 'hasError' => $hasError, 'message' => $message));
    }

    /**
     * Returns more information about an installed module.
     *
     * @throws CHttpException
     */
    public function actionInfo()
    {

        $moduleId = Yii::$app->request->get('moduleId');
        $module = Yii::$app->moduleManager->getModule($moduleId);

        if ($module == null) {
            throw new HttpException(500, Yii::t('AdminModule.controllers_ModuleController', 'Could not find requested module!'));
        }

        $readmeMd = "";
        $readmeMdFile = $module->getBasePath() . DIRECTORY_SEPARATOR . 'README.md';
        if (file_exists($readmeMdFile)) {
            $readmeMd = file_get_contents($readmeMdFile);
        }

        return $this->renderAjax('info', array('name' => $module->getName(), 'description' => $module->getDescription(), 'content' => $readmeMd));
    }

    /**
     * Returns the thirdparty disclaimer
     *
     * @throws HttpException
     */
    public function actionThirdpartyDisclaimer()
    {
        return $this->renderAjax('thirdpartyDisclaimer', array());
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



        $model = new \humhub\modules\admin\models\forms\ModuleSetAsDefaultForm();

        $spaceDefaultModule = null;
        if ($module->hasContentContainerType(Space::className())) {
            $spaceDefaultModule = \humhub\modules\space\models\Module::findOne(['space_id' => 0, 'module_id' => $moduleId]);
            if ($spaceDefaultModule === null) {
                $spaceDefaultModule = new \humhub\modules\space\models\Module();
                $spaceDefaultModule->module_id = $moduleId;
                $spaceDefaultModule->space_id = 0;
                $spaceDefaultModule->state = \humhub\modules\space\models\Module::STATE_DISABLED;
            }
            $model->spaceDefaultState = $spaceDefaultModule->state;
        }

        $userDefaultModule = null;
        if ($module->hasContentContainerType(User::className())) {
            $userDefaultModule = \humhub\modules\user\models\Module::findOne(['user_id' => 0, 'module_id' => $moduleId]);
            if ($userDefaultModule === null) {
                $userDefaultModule = new \humhub\modules\user\models\Module();
                $userDefaultModule->module_id = $moduleId;
                $userDefaultModule->user_id = 0;
                $userDefaultModule->state = \humhub\modules\user\models\Module::STATE_DISABLED;
            }
            $model->userDefaultState = $userDefaultModule->state;
        }


        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            if ($module->hasContentContainerType(Space::className())) {
                $spaceDefaultModule->state = $model->spaceDefaultState;
                $spaceDefaultModule->save();
            }

            if ($module->hasContentContainerType(User::className())) {
                $userDefaultModule->state = $model->userDefaultState;
                $userDefaultModule->save();
            }

            return $this->renderModalClose();
        }

        return $this->renderAjax('setAsDefault', array('module' => $module, 'model' => $model));
    }

    public function getOnlineModuleManager()
    {

        if ($this->_onlineModuleManager === null) {
            $this->_onlineModuleManager = new OnlineModuleManager();
        }

        return $this->_onlineModuleManager;
    }

}
