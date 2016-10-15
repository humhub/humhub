<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\admin\libs;

use ZipArchive;
use Yii;
use yii\web\HttpException;
use yii\base\Exception;

use humhub\libs\CURLHelper;


/**
 * Handles remote module installation, updates and module listing
 *
 * @author luke
 */
class OnlineModuleManager
{

    private $_modules = null;

    /**
     * Installs latest compatible module version
     *
     * @param type $moduleId
     */
    public function install($moduleId)
    {
        $modulePath = Yii::getAlias(Yii::$app->params['moduleMarketplacePath']);

        if (!is_writable($modulePath)) {
            throw new HttpException(500, Yii::t('AdminModule.libs_OnlineModuleManager', 'Module directory %modulePath% is not writeable!', array('%modulePath%' => $modulePath)));
        }

        $moduleInfo = $this->getModuleInfo($moduleId);

        if (!isset($moduleInfo['latestCompatibleVersion'])) {
            throw new Exception(Yii::t('AdminModule.libs_OnlineModuleManager', "No compatible module version found!"));
        }


        $moduleDir = $modulePath . DIRECTORY_SEPARATOR . $moduleId;
        if (is_dir($moduleDir)) {
            $files = new \RecursiveIteratorIterator(
                    new \RecursiveDirectoryIterator($moduleDir, \RecursiveDirectoryIterator::SKIP_DOTS), \RecursiveIteratorIterator::CHILD_FIRST
            );

            foreach ($files as $fileinfo) {
                $todo = ($fileinfo->isDir() ? 'rmdir' : 'unlink');
                $todo($fileinfo->getRealPath());
            }

            rmdir($moduleDir);
            #throw new HttpException(500, Yii::t('AdminModule.libs_OnlineModuleManager', 'Module directory for module %moduleId% already exists!', array('%moduleId%' => $moduleId)));
        }

        // Check Module Folder exists
        $moduleDownloadFolder = Yii::getAlias("@runtime/module_downloads");
        if (!is_dir($moduleDownloadFolder)) {
            if (!@mkdir($moduleDownloadFolder)) {
                throw new Exception("Could not create module download folder!");
            }
        }

        $version = $moduleInfo['latestCompatibleVersion'];

        // Download
        $downloadUrl = $version['downloadUrl'];
        $downloadTargetFileName = $moduleDownloadFolder . DIRECTORY_SEPARATOR . basename($downloadUrl);
        try {
            $http = new \Zend\Http\Client($downloadUrl, array(
                'adapter' => '\Zend\Http\Client\Adapter\Curl',
                'curloptions' => CURLHelper::getOptions(),
                'timeout' => 30
            ));

            $response = $http->send();

            file_put_contents($downloadTargetFileName, $response->getBody());
        } catch (Exception $ex) {
            throw new HttpException('500', Yii::t('AdminModule.libs_OnlineModuleManager', 'Module download failed! (%error%)', array('%error%' => $ex->getMessage())));
        }

        // Extract Package
        if (file_exists($downloadTargetFileName)) {
            $zip = new ZipArchive;
            $res = $zip->open($downloadTargetFileName);
            if ($res === TRUE) {
                $zip->extractTo($modulePath);
                $zip->close();
            } else {
                throw new HttpException('500', Yii::t('AdminModule.libs_OnlineModuleManager', 'Could not extract module!'));
            }
        } else {
            throw new HttpException('500', Yii::t('AdminModule.libs_OnlineModuleManager', 'Download of module failed!'));
        }

        Yii::$app->moduleManager->flushCache();
        Yii::$app->moduleManager->register($modulePath . DIRECTORY_SEPARATOR . $moduleId);
    }

    /**
     * Updates a given module
     *
     * @param HWebModule $module
     */
    public function update($moduleId)
    {
        // Temporary disable module if enabled
        if (Yii::$app->hasModule($moduleId)) {
            Yii::$app->setModule($moduleId, null);
        }

        Yii::$app->moduleManager->removeModule($moduleId, false);

        $this->install($moduleId);

        $updatedModule = Yii::$app->moduleManager->getModule($moduleId);
        $updatedModule->migrate();
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
    public function getModules($cached = true)
    {
        if (!$cached) {
            $this->_modules = null;
            Yii::$app->cache->delete('onlineModuleManager_modules');
        }

        if ($this->_modules !== null) {
            return $this->_modules;
        }

        $this->_modules = Yii::$app->cache->get('onlineModuleManager_modules');
        if ($this->_modules === null || !is_array($this->_modules)) {

            $this->_modules = HumHubAPI::request('v1/modules/list');
            Yii::$app->cache->set('onlineModuleManager_modules', $this->_modules, Yii::$app->settings->get('cache.expireTime'));
        }

        return $this->_modules;
    }

    public function getModuleUpdates()
    {
        $updates = array();

        foreach ($this->getModules() as $moduleId => $moduleInfo) {

            if (isset($moduleInfo['latestCompatibleVersion']) && Yii::$app->moduleManager->hasModule($moduleId)) {

                $module = Yii::$app->moduleManager->getModule($moduleId);

                if ($module !== null) {
                    if (version_compare($moduleInfo['latestCompatibleVersion'], $module->getVersion(), 'gt')) {
                        $updates[$moduleId] = $moduleInfo;
                    }
                } else {
                    Yii::error("Could not load module: " . $moduleId . " to get updates");
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
        return HumHubAPI::request('v1/modules/info', ['id' => $moduleId]);
    }

}
