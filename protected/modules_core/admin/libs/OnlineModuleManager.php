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
 * Handles remote module installation, updates and module listing
 *
 * @author luke
 */
class OnlineModuleManager
{

    /**
     * URL to the HumHub Module Store API
     */
    const HUMHUB_ONLINE_API_URL = "https://www.humhub.org/modules/api/";

    private $_modules = null;

    /**
     * Installs latest compatible module version
     *
     * @param type $moduleId
     */
    public function install($moduleId)
    {
        $modulePath = Yii::app()->getModulePath();

        if (!is_writable($modulePath)) {
            throw new CHttpException(500, Yii::t('AdminModule.libs_OnlineModuleManager', 'Module directory %modulePath% is not writeable!', array('%modulePath%' => $modulePath)));
        }

        $moduleInfo = $this->getModuleInfo($moduleId);

        if (!isset($moduleInfo['latestCompatibleVersion'])) {
            throw new CException(Yii::t('AdminModule.libs_OnlineModuleManager', "No compatible module version found!"));
        }

        if (is_dir($modulePath . DIRECTORY_SEPARATOR . $moduleId)) {
            throw new CHttpException(500, Yii::t('AdminModule.libs_OnlineModuleManager', 'Module directory for module %moduleId% already exists!', array('%moduleId%' => $moduleId)));
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
                'curloptions' => $this->getCurlOptions(),
                'timeout' => 30
            ));
            $response = $http->request();
            file_put_contents($downloadTargetFileName, $response->getBody());
        } catch (Exception $ex) {
            throw new CHttpException('500', Yii::t('AdminModule.libs_OnlineModuleManager', 'Module download failed! (%error%)', array('%error%' => $ex->getMessage())));
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
                throw new CHttpException('500', Yii::t('AdminModule.libs_OnlineModuleManager', 'Could not extract module!'));
            }
        } else {
            throw new CHttpException('500', Yii::t('AdminModule.libs_OnlineModuleManager', 'Download of module failed!'));
        }

        ModuleManager::flushCache();

        // Call Modules autostart
        $autostartFilename = $modulePath . DIRECTORY_SEPARATOR . $moduleId . DIRECTORY_SEPARATOR . 'autostart.php';
        if (file_exists($autostartFilename)) {
            require_once($autostartFilename);
            $module = Yii::app()->moduleManager->getModule($moduleId);
            $module->install();
        }
    }

    /**
     * Updates a given module
     * 
     * @param HWebModule $module
     */
    public function update($moduleId)
    {
        // Hack: for some broken modules using wall aliases
        Yii::setPathOfAlias('wall', Yii::app()->getModulePath());

        // Remove old module files
        Yii::app()->moduleManager->removeModuleFolder($moduleId);
        $this->install($moduleId);

        $module = Yii::app()->moduleManager->getModule($moduleId);
        $module->update();
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
    public function getModules()
    {

        if ($this->_modules !== null) {
            return $this->_modules;
        }

        $url = self::HUMHUB_ONLINE_API_URL . "list?version=" . urlencode(HVersion::VERSION) . "&installId=" . HSetting::Get('installationId', 'admin');

        try {

            $this->_modules = Yii::app()->cache->get('onlineModuleManager_modules');
            if ($this->_modules === null || !is_array($this->_modules)) {

                $http = new Zend_Http_Client($url, array(
                    'adapter' => 'Zend_Http_Client_Adapter_Curl',
                    'curloptions' => $this->getCurlOptions(),
                    'timeout' => 30
                ));

                $response = $http->request();
                $json = $response->getBody();

                $this->_modules = CJSON::decode($json);
                Yii::app()->cache->set('onlineModuleManager_modules', $this->_modules, HSetting::Get('expireTime', 'cache'));
            }
        } catch (Exception $ex) {
            throw new CHttpException('500', Yii::t('AdminModule.libs_OnlineModuleManager', 'Could not fetch module list online! (%error%)', array('%error%' => $ex->getMessage())));
        }
        return $this->_modules;
    }

    public function getModuleUpdates()
    {
        $updates = array();

        foreach ($this->getModules() as $moduleId => $moduleInfo) {

            if (isset($moduleInfo['latestCompatibleVersion']) && Yii::app()->moduleManager->isInstalled($moduleId)) {

                $module = Yii::app()->moduleManager->getModule($moduleId);

                if (version_compare($moduleInfo['latestCompatibleVersion'], $module->getVersion(), 'gt')) {
                    $updates[$moduleId] = $moduleInfo;
                }
            }
        }

        return $updates;
    }

    /**
     * Returns an array of informations about a module
     */
    public function getModuleInfo($moduleId)
    {

        // get all module informations
        $url = self::HUMHUB_ONLINE_API_URL . "info?id=" . urlencode($moduleId) . "&version=" . HVersion::VERSION . "&installId=" . HSetting::Get('installationId', 'admin');
        try {

            $http = new Zend_Http_Client($url, array(
                'adapter' => 'Zend_Http_Client_Adapter_Curl',
                'curloptions' => $this->getCurlOptions(),
                'timeout' => 30
            ));

            $response = $http->request();
            $json = $response->getBody();

            $moduleInfo = CJSON::decode($json);
        } catch (Exception $ex) {
            throw new CHttpException('500', Yii::t('AdminModule.libs_OnlineModuleManager', 'Could not get module info online! (%error%)', array('%error%' => $ex->getMessage())));
        }

        return $moduleInfo;
    }

    private function getCurlOptions()
    {
        return array(
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_CAINFO => Yii::getPathOfAlias('application.config.ssl_certs') . DIRECTORY_SEPARATOR . 'humhub.crt'
        );
    }

}
