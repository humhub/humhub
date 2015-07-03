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

namespace humhub\modules\admin\controllers;

use Yii;
use yii\helpers\Url;
use humhub\components\Controller;
use humhub\modules\admin\libs\OnlineModuleManager;

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

    public function behaviors()
    {
        return [
            'acl' => [
                'class' => \humhub\components\behaviors\AccessControl::className(),
                'adminOnly' => true
            ]
        ];
    }

    public function actionIndex()
    {
        Yii::$app->cache->delete(\humhub\components\bootstrap\ModuleAutoLoader::CACHE_ID);
        return $this->redirect(Url::to(['/admin/module/list']));
    }

    public function actionList()
    {
        $installedModules = Yii::$app->moduleManager->getModules();
        return $this->render('list', array('installedModules' => $installedModules));
    }

    /**
     * Enables a module
     *
     * @throws CHttpException
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

        return $this->redirect(Url::toRoute('/admin/module/list'));
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

        return $this->redirect(Url::to(['/admin/module/list']));
    }

    /**
     * Installs a given moduleId from marketplace
     */
    public function actionInstall()
    {

        $this->forcePostRequest();

        $moduleId = Yii::$app->request->getQuery('moduleId');

        if (!Yii::$app->moduleManager->hasModule($moduleId)) {
            $onlineModules = new OnlineModuleManager();
            $onlineModules->install($moduleId);
        }

        // Redirect to Module Install?
        $this->redirect(Yii::$app->createUrl('admin/module/list'));
    }

    /**
     * Uninstalls a custom module
     *
     * @throws CHttpException
     */
    public function actionUninstall()
    {

        $this->forcePostRequest();

        $moduleId = Yii::$app->request->getQuery('moduleId');

        if (Yii::$app->moduleManager->hasModule($moduleId)) {

            $module = Yii::$app->moduleManager->getModule($moduleId);

            if ($module == null) {
                throw new CHttpException(500, Yii::t('AdminModule.controllers_ModuleController', 'Could not find requested module!'));
            }

            if (!is_writable($module->getPath())) {
                throw new CHttpException(500, Yii::t('AdminModule.controllers_ModuleController', 'Module path %path% is not writeable!', array('%path%' => $module->getPath())));
            }

            $module->uninstall();
        }
        $this->redirect(Yii::$app->createUrl('admin/module/list'));
    }

    /**
     * Updates a module with the most recent version online
     *
     * @throws CHttpException
     */
    public function actionUpdate()
    {

        $this->forcePostRequest();

        $moduleId = Yii::$app->request->getQuery('moduleId');
        $module = Yii::$app->moduleManager->getModule($moduleId);

        if ($module == null) {
            throw new CHttpException(500, Yii::t('AdminModule.controllers_ModuleController', 'Could not find requested module!'));
        }

        if (!Yii::$app->moduleManager->canUninstall($moduleId)) {
            throw new CHttpException(500, Yii::t('AdminModule.controllers_ModuleController', 'Could not uninstall module first! Module is protected.'));
        }

        $onlineModules = $this->getOnlineModuleManager();
        $onlineModules->update($moduleId);

        $this->redirect(Yii::$app->createUrl('admin/module/list'));
    }

    /**
     * Complete list of all modules
     */
    public function actionListOnline()
    {
        $keyword = Yii::$app->request->get('keyword', "");

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
     * Returns more information about an installed module.
     *
     * @throws CHttpException
     */
    public function actionInfo()
    {

        $moduleId = Yii::$app->request->getQuery('moduleId');
        $module = Yii::$app->moduleManager->getModule($moduleId);

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
        $moduleId = Yii::$app->request->get('moduleId');
        $module = Yii::$app->moduleManager->getModule($moduleId);

        if ($module == null) {
            throw new HttpException(500, Yii::t('AdminModule.controllers_ModuleController', 'Could not find requested module!'));
        }

        $model = new \humhub\modules\admin\models\forms\ModuleSetAsDefaultForm();

        $spaceDefaultModule = null;
        if ($module->isSpaceModule()) {
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
        if ($module->isUserModule()) {
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
            if ($module->isSpaceModule()) {
                $spaceDefaultModule->state = $model->spaceDefaultState;
                $spaceDefaultModule->save();
            }

            if ($module->isUserModule()) {
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
