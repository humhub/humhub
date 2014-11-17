<?php

/**
 * HumHub
 * Copyright © 2014 The HumHub Project
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 */

/**
 * Module Controller controls all third party modules in a humhub installation.
 *
 * @package humhub.modules_core.admin.controllers
 * @since 0.5
 */
class ModuleController extends Controller
{

    public $subLayout = "/_layout";
    private $_onlineModuleManager = null;

    /**
     * @return array action filters
     */
    public function filters()
    {
        return array(
            'accessControl', // perform access control for CRUD operations
        );
    }

    /**
     * Specifies the access control rules.
     * This method is used by the 'accessControl' filter.
     * @return array access control rules
     */
    public function accessRules()
    {
        return array(
            array('allow',
                'expression' => 'Yii::app()->user->isAdmin()'
            ),
            array('deny', // deny all users
                'users' => array('*'),
            ),
        );
    }

    public function actionIndex()
    {
        ModuleManager::flushCache();

        // Require this initial redirect to ensure Module Cache is flushed
        // before list it.
        $this->redirect(Yii::app()->createUrl('admin/module/list'));
    }

    public function actionList()
    {

        $installedModules = Yii::app()->moduleManager->getInstalledModules();
        ModuleManager::flushCache();

        $this->render('list', array('installedModules' => $installedModules));
    }

    /**
     * Enables a module
     *
     * @throws CHttpException
     */
    public function actionEnable()
    {

        $this->forcePostRequest();

        $moduleId = Yii::app()->request->getQuery('moduleId');
        $module = Yii::app()->moduleManager->getModule($moduleId);

        if ($module == null) {
            throw new CHttpException(500, Yii::t('AdminModule.controllers_ModuleController', 'Could not find requested module!'));
        }

        $module->enable();

        $this->redirect(Yii::app()->createUrl('admin/module/list'));
    }

    /**
     * Disables a module
     *
     * @throws CHttpException
     */
    public function actionDisable()
    {

        $this->forcePostRequest();

        $moduleId = Yii::app()->request->getQuery('moduleId');
        $module = Yii::app()->moduleManager->getModule($moduleId);

        if ($module == null) {
            throw new CHttpException(500, Yii::t('AdminModule.controllers_ModuleController', 'Could not find requested module!'));
        }

        $module->disable();

        $this->redirect(Yii::app()->createUrl('admin/module/list'));
    }

    /**
     * Installs a given moduleId from marketplace
     */
    public function actionInstall()
    {

        $this->forcePostRequest();

        $moduleId = Yii::app()->request->getQuery('moduleId');

        if (!Yii::app()->moduleManager->isInstalled($moduleId)) {
            $onlineModules = new OnlineModuleManager();
            $onlineModules->install($moduleId);
        }

        // Redirect to Module Install?
        $this->redirect(Yii::app()->createUrl('admin/module/list'));
    }

    /**
     * Uninstalls a custom module
     *
     * @throws CHttpException
     */
    public function actionUninstall()
    {

        $this->forcePostRequest();

        $moduleId = Yii::app()->request->getQuery('moduleId');

        if (Yii::app()->moduleManager->isInstalled($moduleId)) {

            $module = Yii::app()->moduleManager->getModule($moduleId);

            if ($module == null) {
                throw new CHttpException(500, Yii::t('AdminModule.controllers_ModuleController', 'Could not find requested module!'));
            }

            if (!is_writable($module->getPath())) {
                throw new CHttpException(500, Yii::t('AdminModule.controllers_ModuleController', 'Module path %path% is not writeable!', array('%path%' => $module->getPath())));
            }

            $module->uninstall();
        }
        $this->redirect(Yii::app()->createUrl('admin/module/list'));
    }

    /**
     * Updates a module with the most recent version online
     *
     * @throws CHttpException
     */
    public function actionUpdate()
    {

        $this->forcePostRequest();

        $moduleId = Yii::app()->request->getQuery('moduleId');
        $module = Yii::app()->moduleManager->getModule($moduleId);

        if ($module == null) {
            throw new CHttpException(500, Yii::t('AdminModule.controllers_ModuleController', 'Could not find requested module!'));
        }

        if (!Yii::app()->moduleManager->canUninstall($moduleId)) {
            throw new CHttpException(500, Yii::t('AdminModule.controllers_ModuleController', 'Could not uninstall module first! Module is protected.'));
        }

        $onlineModules = $this->getOnlineModuleManager();
        $onlineModules->update($moduleId);

        $this->redirect(Yii::app()->createUrl('admin/module/list'));
    }

    /**
     * Complete list of all modules
     */
    public function actionListOnline()
    {
        $keyword = Yii::app()->request->getParam('keyword', "");

        $onlineModules = $this->getOnlineModuleManager();
        $modules = $onlineModules->getModules();

        if ($keyword != "") {
            $results = array();
            foreach ($modules as $module) {
                if (stripos($module['name'], $keyword) !== false || stripos($module['description'], $keyword) !== false) {
                    array_push($results, $module);
                }
            }
            $modules = $results;
        }

        $this->render('listOnline', array('modules' => $modules, 'keyword' => $keyword));

    }

    /**
     * Lists all available module updates
     */
    public function actionListUpdates()
    {
        $onlineModules = $this->getOnlineModuleManager();
        $modules = $onlineModules->getModuleUpdates();

        $this->render('listUpdates', array('modules' => $modules));
    }

    /**
     * Returns more information about an installed module.
     *
     * @throws CHttpException
     */
    public function actionInfo()
    {

        $moduleId = Yii::app()->request->getQuery('moduleId');
        $module = Yii::app()->moduleManager->getModule($moduleId);

        if ($module == null) {
            throw new CHttpException(500, Yii::t('AdminModule.controllers_ModuleController', 'Could not find requested module!'));
        }

        $readmeMd = "";
        $readmeMdFile = $module->getPath() . DIRECTORY_SEPARATOR . 'README.md';
        if (file_exists($readmeMdFile)) {
            $readmeMd = file_get_contents($readmeMdFile);
        }

        $this->renderPartial('info', array('name' => $module->getName(), 'description' => $module->getDescription(), 'content' => $readmeMd), false, true);
    }

    /**
     * Sets default enabled/disabled on User or/and Space Modules
     *
     * @throws CHttpException
     */
    public function actionSetAsDefault()
    {

        $moduleId = Yii::app()->request->getQuery('moduleId');
        $module = Yii::app()->moduleManager->getModule($moduleId);

        if ($module == null) {
            throw new CHttpException(500, Yii::t('AdminModule.controllers_ModuleController', 'Could not find requested module!'));
        }

        $model = new ModuleSetAsDefaultForm();

        $spaceDefaultModule = null;
        if ($module->isSpaceModule()) {
            $spaceDefaultModule = SpaceApplicationModule::model()->findByAttributes(array('space_id' => 0, 'module_id' => $moduleId));
            if ($spaceDefaultModule === null) {
                $spaceDefaultModule = new SpaceApplicationModule();
                $spaceDefaultModule->module_id = $moduleId;
                $spaceDefaultModule->space_id = 0;
                $spaceDefaultModule->state = SpaceApplicationModule::STATE_DISABLED;
            }
            $model->spaceDefaultState = $spaceDefaultModule->state;
        }

        $userDefaultModule = null;
        if ($module->isUserModule()) {
            $userDefaultModule = UserApplicationModule::model()->findByAttributes(array('user_id' => 0, 'module_id' => $moduleId));
            if ($userDefaultModule === null) {
                $userDefaultModule = new UserApplicationModule();
                $userDefaultModule->module_id = $moduleId;
                $userDefaultModule->user_id = 0;
                $userDefaultModule->state = UserApplicationModule::STATE_DISABLED;
            }
            $model->userDefaultState = $userDefaultModule->state;
        }


        if (isset($_POST['ModuleSetAsDefaultForm'])) {


            $_POST['ModuleSetAsDefaultForm'] = Yii::app()->input->stripClean($_POST['ModuleSetAsDefaultForm']);
            $model->attributes = $_POST['ModuleSetAsDefaultForm'];

            if ($model->validate()) {

                if ($module->isSpaceModule()) {
                    $spaceDefaultModule->state = $model->spaceDefaultState;
                    $spaceDefaultModule->save();
                }

                if ($module->isUserModule()) {
                    $userDefaultModule->state = $model->userDefaultState;
                    $userDefaultModule->save();
                }

                // close modal
                $this->renderModalClose();
            }
        }

        $this->renderPartial('setAsDefault', array('module' => $module, 'model' => $model), false, true);
    }

    public function getOnlineModuleManager()
    {

        if ($this->_onlineModuleManager === null) {
            $this->_onlineModuleManager = new OnlineModuleManager();
        }

        return $this->_onlineModuleManager;
    }

}
