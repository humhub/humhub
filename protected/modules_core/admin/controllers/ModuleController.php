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
            throw new CHttpException(500, Yii::t('AdminModule.modules', 'Could not find requested module!'));
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
            throw new CHttpException(500, Yii::t('AdminModule.modules', 'Could not find requested module!'));
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
                throw new CHttpException(500, Yii::t('AdminModule.modules', 'Could not find requested module!'));
            }

            if (!is_writable($module->getPath())) {
                throw new CHttpException(500, Yii::t('AdminModule.modules', 'Module path %path% is not writeable!', array('%path%' => $module->getPath())));
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
            throw new CHttpException(500, Yii::t('AdminModule.modules', 'Could not find requested module!'));
        }

        if (!Yii::app()->moduleManager->canUninstall($moduleId)) {
            throw new CHttpException(500, Yii::t('AdminModule.modules', 'Could not uninstall module first! Module is protected.'));
        }

        $onlineModules = new OnlineModuleManager();
        $onlineModules->update($module);
        
        $this->redirect(Yii::app()->createUrl('admin/module/list'));
    }

    /**
     * Complete list of all modules
     */
    public function actionListOnline()
    {
        $onlineModules = new OnlineModuleManager();
        $modules = $onlineModules->getModules();
        $this->render('listOnline', array('modules' => $modules));
    }

    /**
     * Lists all available module updates
     */
    public function actionListUpdates()
    {

        $updates = array();

        $onlineModules = new OnlineModuleManager();
        foreach ($onlineModules->getModules() as $moduleId => $moduleInfo) {

            if (isset($moduleInfo['latestCompatibleVersion']) && Yii::app()->moduleManager->isInstalled($moduleId)) {

                $module = Yii::app()->moduleManager->getModule($moduleId);

                if (version_compare($moduleInfo['latestCompatibleVersion'], $module->getVersion(), 'gt')) {
                    $updates[$moduleId] = $moduleInfo;
                }
            }
        }

        $this->render('listUpdates', array('modules' => $updates));
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
            throw new CHttpException(500, Yii::t('AdminModule.modules', 'Could not find requested module!'));
        }

        $readmeMd = "";
        $readmeMdFile = $module->getPath() . DIRECTORY_SEPARATOR . 'README.md';
        if (file_exists($readmeMdFile)) {
            $readmeMd = file_get_contents($readmeMdFile);
        }

        $this->renderPartial('info', array('name' => $module->getName(), 'description' => $module->getDescription(), 'content' => $readmeMd), false, true);
    }

    /**
     * Returns informations about a online not installed module
     * 
     * @throws CHttpException
     */
    public function actionInfoOnline()
    {

        $moduleId = Yii::app()->request->getQuery('moduleId');

        $onlineModules = new OnlineModuleManager();
        $moduleInfo = $onlineModules->getModuleInfo($moduleId);

        if (!isset($moduleInfo['latestVersion'])) {
            throw new CException(Yii::t('AdminModule.modules', "No module version found!"));
        }

        $this->renderPartial('info', array('name' => $moduleInfo['latestVersion']['name'], 'description' => $moduleInfo['latestVersion']['description'], 'content' => $moduleInfo['latestVersion']['README.md']), false, true);
    }


}
