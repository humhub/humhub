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

    /**
     * URL to the HumHub Module Store API
     */
    const HUMHUB_ONLINE_API_URL = "https://www.humhub.org/modules/api/";

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
            $this->install($moduleId);
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

        // Remove old module files
        $module->removeModuleFolder();
        $this->install($moduleId);
        $module->update();

        $this->redirect(Yii::app()->createUrl('admin/module/list'));
    }

    /**
     * Complete list of all modules
     */
    public function actionListOnline()
    {
        $modules = $this->getOnlineModules();
        $this->render('listOnline', array('modules' => $modules));
    }

    /**
     * Lists all available module updates
     */
    public function actionListUpdates()
    {

        $updates = array();

        foreach ($this->getOnlineModules() as $moduleId => $moduleInfo) {

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

        $moduleInfo = $this->getOnlineModuleInfo($moduleId);

        if (!isset($moduleInfo['latestVersion'])) {
            throw new CException(Yii::t('AdminModule.modules', "No module version found!"));
        }

        $this->renderPartial('info', array('name' => $moduleInfo['latestVersion']['name'], 'description' => $moduleInfo['latestVersion']['description'], 'content' => $moduleInfo['latestVersion']['README.md']), false, true);
    }

    /**
     * Installs latest compatible module version 
     * 
     * @param type $moduleId
     */
    private function install($moduleId)
    {
        $modulePath = Yii::app()->getModulePath();

        if (!is_writable($modulePath)) {
            throw new CHttpException(500, Yii::t('AdminModule.modules', 'Module directory %modulePath% is not writeable!', array('%modulePath%' => $modulePath)));
        }

        $moduleInfo = $this->getOnlineModuleInfo($moduleId);

        if (!isset($moduleInfo['latestCompatibleVersion'])) {
            throw new CException(Yii::t('AdminModule.modules', "No compatible module version found!"));
        }

        if (is_dir($modulePath . DIRECTORY_SEPARATOR . $moduleId)) {
            throw new CHttpException(500, Yii::t('AdminModule.modules', 'Module directory for module %moduleId% already exists!', array('%moduleId%' => $moduleId)));
        }

        // Check Module Folder exists
        $moduleDownloadFolder = Yii::app()->getRuntimePath() . DIRECTORY_SEPARATOR . 'module_downloads';
        if (!is_dir($moduleDownloadFolder)) {
            if (!@mkdir($moduleDownloadFolder)) {
                throw new CException("Could not create module download folder!");
            }
        }

        $version = $moduleInfo['latestCompatibleVersion'];

        // Download
        $downloadUrl = $version['downloadUrl'];
        $downloadTargetFileName = $moduleDownloadFolder . DIRECTORY_SEPARATOR . basename($downloadUrl);
        try {
            $http = new Zend_Http_Client($downloadUrl, array(
                'adapter' => 'Zend_Http_Client_Adapter_Curl',
                'curloptions' => array(CURLOPT_SSL_VERIFYPEER => true, CURLOPT_SSL_VERIFYHOST => true),
            ));
            $response = $http->request();
            file_put_contents($downloadTargetFileName, $response->getBody());
        } catch (Exception $ex) {
            throw new CHttpException('500', Yii::t('AdminModule.modules', 'Module download failed! (%error%)', array('%error%' => $ex->getMessage())));
        }

        // Extract Package
        if (file_exists($downloadTargetFileName)) {
            // Unzip
            $zip = new ZipArchive;
            $res = $zip->open($downloadTargetFileName);
            if ($res === TRUE) {
                $zip->extractTo($modulePath);
                $zip->close();
            } else {
                throw new CHttpException('500', Yii::t('AdminModule.modules', 'Could not extract module!'));
            }
        } else {
            throw new CHttpException('500', Yii::t('AdminModule.modules', 'Download of module failed!'));
        }

        ModuleManager::flushCache();
    }

    /**
     * Returns an array of all available online modules
     * 
     * Key is moduleId
     *  - name
     *  - description
     *  - latestVersion
     *  - latestCompatibleVersion
     * 
     * @return Array of modulles
     */
    private function getOnlineModules()
    {
        $url = self::HUMHUB_ONLINE_API_URL . "list?version=" . urlencode(HVersion::VERSION);
        $modules = array();

        try {

            $http = new Zend_Http_Client($url, array(
                'adapter' => 'Zend_Http_Client_Adapter_Curl',
                'curloptions' => array(CURLOPT_SSL_VERIFYPEER => true, CURLOPT_SSL_VERIFYHOST => true),
            ));

            $response = $http->request();
            $json = $response->getBody();

            $modules = CJSON::decode($json);
        } catch (Exception $ex) {
            throw new CHttpException('500', Yii::t('AdminModule.modules', 'Could not fetch module list online! (%error%)', array('%error%' => $ex->getMessage())));
        }
        return $modules;
    }

    /**
     * Returns an array of informations about a module
     */
    private function getOnlineModuleInfo($moduleId)
    {

        // get all module informations
        $url = self::HUMHUB_ONLINE_API_URL . "info?id=" . urlencode($moduleId) . "&version=" . HVersion::VERSION;
        try {
            $http = new Zend_Http_Client($url, array(
                'adapter' => 'Zend_Http_Client_Adapter_Curl',
                'curloptions' => array(CURLOPT_SSL_VERIFYPEER => true, CURLOPT_SSL_VERIFYHOST => true),
            ));


            $response = $http->request();
            $json = $response->getBody();

            $moduleInfo = CJSON::decode($json);
        } catch (Exception $ex) {
            throw new CHttpException('500', Yii::t('AdminModule.modules', 'Could not get module info online! (%error%)', array('%error%' => $ex->getMessage())));
        }

        return $moduleInfo;
    }

}
