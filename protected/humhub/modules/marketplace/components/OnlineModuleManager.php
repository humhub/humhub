<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\marketplace\components;

use humhub\components\ModuleEvent;
use humhub\modules\admin\libs\HumHubAPI;
use humhub\modules\marketplace\models\Module as ModelModule;
use humhub\modules\marketplace\Module;
use Yii;
use yii\base\Component;
use yii\web\HttpException;
use yii\base\Exception;
use yii\helpers\FileHelper;
use ZipArchive;

/**
 * Handles remote module installation, updates and module listing
 *
 * @author luke
 */
class OnlineModuleManager extends Component
{
    const EVENT_BEFORE_UPDATE = 'beforeUpdate';
    const EVENT_AFTER_UPDATE = 'afterUpdate';

    private $_modules = null;

    /**
     * Installs latest compatible module version
     *
     * @param string $moduleId
     * @throws Exception
     * @throws HttpException
     * @throws \yii\base\ErrorException
     * @throws \yii\base\InvalidConfigException
     */
    public function install($moduleId)
    {
        /** @var Module $marketplaceModule */
        $marketplaceModule = Yii::$app->getModule('marketplace');
        $modulesPath = Yii::getAlias($marketplaceModule->modulesPath);

        if (!is_writable($modulesPath)) {
            throw new Exception(Yii::t('MarketplaceModule.base', 'Module directory %modulePath% is not writeable!', ['%modulePath%' => $modulesPath]));
        }

        $moduleInfo = $this->getModuleInfo($moduleId);

        if (!isset($moduleInfo['latestCompatibleVersion'])) {
            throw new Exception(Yii::t('MarketplaceModule.base', 'No compatible module version found!'));
        }

        // Check Module Folder exists
        $moduleDownloadFolder = Yii::getAlias($marketplaceModule->modulesDownloadPath);
        FileHelper::createDirectory($moduleDownloadFolder);

        // Download
        $downloadUrl = $moduleInfo['latestCompatibleVersion']['downloadUrl'];
        $downloadTargetFileName = $moduleDownloadFolder . DIRECTORY_SEPARATOR . basename($downloadUrl);
        try {
            $hashSha256 = $moduleInfo['latestCompatibleVersion']['downloadFileSha256'];
            $this->downloadFile($downloadTargetFileName, $downloadUrl, $hashSha256);
        } catch (\Exception $ex) {
            throw new HttpException('500', Yii::t('MarketplaceModule.base', 'Module download failed! (%error%)', ['%error%' => $ex->getMessage()]));
        }

        // Remove old module path
        if (!$this->removeModuleDir($modulesPath . DIRECTORY_SEPARATOR . $moduleId)) {
            throw new HttpException('500', Yii::t('MarketplaceModule.base', 'Could not remove old module path!'));
        }

        // Extract Package
        if (!file_exists($downloadTargetFileName)) {
            throw new HttpException('500', Yii::t('MarketplaceModule.base', 'Download of module failed!'));
        }

        if (!$this->unzip($downloadTargetFileName, $modulesPath)) {
            Yii::error('Could not unzip ' . $downloadTargetFileName . ' to ' . $modulesPath, 'marketplace');
            throw new HttpException('500', Yii::t('MarketplaceModule.base', 'Could not extract module!'));
        }

        Yii::$app->moduleManager->flushCache();
        Yii::$app->moduleManager->register($modulesPath . DIRECTORY_SEPARATOR . $moduleId);
    }


    private function removeModuleDir($path)
    {
        if (is_dir($path)) {
            $files = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($path, \RecursiveDirectoryIterator::SKIP_DOTS), \RecursiveIteratorIterator::CHILD_FIRST
            );

            foreach ($files as $fileinfo) {
                $todo = ($fileinfo->isDir() ? 'rmdir' : 'unlink');
                $todo($fileinfo->getRealPath());
            }

            FileHelper::removeDirectory($path);
        }

        return (!is_dir($path));
    }

    private function unzip($file, $folder)
    {
        $zip = new ZipArchive;
        $res = $zip->open($file);
        if ($res !== true) {
            return false;
        }
        $zip->extractTo($folder);
        $zip->close();

        return true;
    }

    private function downloadFile($fileName, $url, $sha256 = null)
    {
        if (is_file($fileName) && !empty($sha256) && hash_file('sha256', $fileName) === $sha256) {
            // File already downloaded
            return true;
        }

        $httpClient = new HumHubApiClient();
        try {
            $fp = fopen($fileName, "w");
            $httpClient->get($url)->addOptions(['timeout' => 300])->setOutputFile($fp)->send();
            fclose($fp);
        } catch (\yii\httpclient\Exception $e) {
            throw new \Exception('Download failed.' . $e->getMessage());
        }

        if (!is_file($fileName)) {
            throw new \Exception('Download failed. Could not write file! ' . $fileName);
        }

        if (!empty($sha256) && hash_file('sha256', $fileName) !== $sha256) {
            throw new \Exception('File verification failed. Could not download file! ' . $fileName);
        }

        return true;
    }


    /**
     * Updates a given module
     *
     * @param string $moduleId
     * @throws Exception
     * @throws HttpException
     * @throws \yii\base\ErrorException
     * @throws \yii\base\InvalidConfigException
     */
    public function update($moduleId)
    {
        $this->trigger(static::EVENT_BEFORE_UPDATE, new ModuleEvent(['module' => Yii::$app->moduleManager->getModule($moduleId)]));

        // Temporary disable module if enabled
        if (Yii::$app->hasModule($moduleId)) {
            Yii::$app->setModule($moduleId, null);
        }

        Yii::$app->moduleManager->removeModule($moduleId, false);

        $this->install($moduleId);

        $updatedModule = Yii::$app->moduleManager->getModule($moduleId);
        $updatedModule->migrate();

        $this->trigger(static::EVENT_AFTER_UPDATE, new ModuleEvent(['module' => $updatedModule]));
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
     * @return array of modules
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

        /** @var Module $module */
        $module = Yii::$app->getModule('marketplace');

        $this->_modules = Yii::$app->cache->get('onlineModuleManager_modules');
        if ($this->_modules === null || !is_array($this->_modules)) {
            $this->_modules = HumHubAPI::request('v1/modules/list', [
                'includeBetaVersions' => (boolean)$module->settings->get('includeBetaUpdates')
            ]);

            foreach ($module->moduleBlacklist as $blacklistedModuleId) {
                unset($this->_modules[$blacklistedModuleId]);
            }

            Yii::$app->cache->set('onlineModuleManager_modules', $this->_modules, Yii::$app->settings->get('cache.expireTime'));
        }

        return $this->_modules;
    }


    public function getCategories()
    {
        return Yii::$app->cache->getOrSet('marketplace-categories', function () {

            $categories = HumHubAPI::request('v1/modules/list-categories');

            $names = [];
            $names[0] = 'All categories (' . count($this->_modules) . ')';

            foreach ($categories as $i => $n) {
                $c = 0;
                foreach ($this->_modules as $m) {
                    if (in_array($i, $m['categories'])) {
                        $c++;
                    }
                }
                $names[$i] = $n['name'] . ' (' . $c . ')';
            }

            return $names;
        });
    }


    public function getModuleUpdates($cached = true)
    {
        $updates = [];

        foreach ($this->getModules($cached) as $moduleId => $moduleInfo) {

            if (isset($moduleInfo['latestCompatibleVersion']) && Yii::$app->moduleManager->hasModule($moduleId)) {

                $module = Yii::$app->moduleManager->getModule($moduleId);

                if ($module !== null) {
                    if (version_compare($moduleInfo['latestCompatibleVersion'], $module->getVersion(), 'gt')) {
                        $updates[$moduleId] = $moduleInfo;
                    }
                } else {
                    Yii::error('Could not load module: ' . $moduleId . ' to get updates');
                }
            }
        }

        return $updates;
    }

    /**
     * Returns an array of informations about a module
     *
     * @return array
     */
    public function getModuleInfo($moduleId)
    {
        /** @var Module $module */
        $module = Yii::$app->getModule('marketplace');

        if (in_array($moduleId, $module->moduleBlacklist)) {
            return [];
        }

        return HumHubAPI::request('v1/modules/info', [
            'id' => $moduleId, 'includeBetaVersions' => (boolean)$module->settings->get('includeBetaUpdates')
        ]);
    }

    /**
     * Get only not installed modules
     *
     * @return ModelModule[]
     */
    public function getNotInstalledModules(): array
    {
        /** @var Module $module */
        $marketplaceModule = Yii::$app->getModule('marketplace');

        $modules = $this->getModules();

        foreach ($modules as $o => $module) {
            $onlineModule = new ModelModule($module);
            if ($onlineModule->isInstalled() ||
                !$onlineModule->latestCompatibleVersion ||
                ($onlineModule->isDeprecated && $marketplaceModule->hideLegacyModules)) {
                unset($modules[$o]);
                continue;
            }
            $modules[$o] = $onlineModule;
        }

        return $modules;
    }

    /**
     * Get modules with available update
     *
     * @return ModelModule[]
     */
    public function getAvailableUpdateModules(): array
    {
        $modules = $this->getModuleUpdates(false);

        foreach ($modules as $o => $module) {
            $modules[$o] = new ModelModule($module);
        }

        return $modules;
    }

}
