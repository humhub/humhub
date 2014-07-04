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

    /**
     * Installs latest compatible module version
     *
     * @param type $moduleId
     */
    public function install($moduleId)
    {
        $modulePath = Yii::app()->getModulePath();

        if (!is_writable($modulePath)) {
            throw new CHttpException(500, Yii::t('AdminModule.modules', 'Module directory %modulePath% is not writeable!', array('%modulePath%' => $modulePath)));
        }

        $moduleInfo = $this->getModuleInfo($moduleId);

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
                'curloptions' => $this->getCurlOptions(),
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

        // Call Modules autostart
        $autostartFilename = $modulePath . DIRECTORY_SEPARATOR . $moduleId . DIRECTORY_SEPARATOR . 'autostart.php';
        if (file_exists($autostartFilename)) {
            require_once($autostartFilename);
            $module = Yii::app()->moduleManager->getModule($moduleId);
            $module->install();
        }
    }

    public function update(HWebModule $module)
    {

        // Remove old module files
		Yii::app()->moduleManager->removeModuleFolder($module->getId());
        $this->install($module->getId());
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
        $url = self::HUMHUB_ONLINE_API_URL . "list?version=" . urlencode(HVersion::VERSION)."&installId=".HSetting::Get('installationId', 'admin');
        $modules = array();

        try {

            $http = new Zend_Http_Client($url, array(
                'adapter' => 'Zend_Http_Client_Adapter_Curl',
                'curloptions' => $this->getCurlOptions(),
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
    public function getModuleInfo($moduleId)
    {

        // get all module informations
        $url = self::HUMHUB_ONLINE_API_URL . "info?id=" . urlencode($moduleId) . "&version=" . HVersion::VERSION."&installId=".HSetting::Get('installationId', 'admin');
        try {
            $http = new Zend_Http_Client($url, array(
                'adapter' => 'Zend_Http_Client_Adapter_Curl',
                'curloptions' => $this->getCurlOptions(),
            ));

            $response = $http->request();
            $json = $response->getBody();

            $moduleInfo = CJSON::decode($json);
        } catch (Exception $ex) {
            throw new CHttpException('500', Yii::t('AdminModule.modules', 'Could not get module info online! (%error%)', array('%error%' => $ex->getMessage())));
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
