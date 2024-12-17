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
use humhub\modules\marketplace\services\MarketplaceService;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Yii;
use yii\base\Component;
use yii\base\ErrorException;
use yii\base\Exception;
use yii\base\InvalidConfigException;
use yii\helpers\FileHelper;
use yii\web\HttpException;
use yii\web\ServerErrorHttpException;
use ZipArchive;

/**
 * Handles remote module installation, updates and module listing
 *
 * @author luke
 */
class OnlineModuleManager extends Component
{
    public const EVENT_BEFORE_UPDATE = 'beforeUpdate';
    public const EVENT_AFTER_UPDATE = 'afterUpdate';

    private $_modules = null;

    /**
     * Installs latest compatible module version
     *
     * @param string $moduleId
     * @return void
     * @throws Exception
     * @throws HttpException
     * @throws InvalidConfigException
     * @throws ServerErrorHttpException
     */
    public function install($moduleId)
    {
        /** @var Module $marketplaceModule */
        $marketplaceModule = Yii::$app->getModule('marketplace');
        $modulesPath = realpath(Yii::getAlias($marketplaceModule->modulesPath));

        if (!is_writable($modulesPath)) {
            $this->throwError($moduleId, Yii::t('MarketplaceModule.base', 'Module directory %modulePath% is not writeable!', ['%modulePath%' => $modulesPath]));
        }

        $moduleInfo = $this->getModuleInfo($moduleId);

        if (!isset($moduleInfo['latestCompatibleVersion'])) {
            $this->throwError($moduleId, Yii::t('MarketplaceModule.base', 'No compatible module version found!'));
        }

        $downloadTargetFileName = $this->downloadModule($moduleId);
        $this->checkRequirements($moduleId, $downloadTargetFileName);

        // Remove old module path
        if (!$this->removeModuleDir($modulesPath . DIRECTORY_SEPARATOR . $moduleId)) {
            $this->throwError($moduleId, Yii::t('MarketplaceModule.base', 'Could not remove old module path!'));
        }

        if (!$this->unzip($downloadTargetFileName, $modulesPath)) {
            $this->throwError(
                $moduleId,
                'Could not unzip ' . $downloadTargetFileName . ' to ' . $modulesPath,
                Yii::t('MarketplaceModule.base', 'Could not extract module!'),
            );
        }

        Yii::$app->moduleManager->flushCache();
        Yii::$app->moduleManager->register($modulesPath . DIRECTORY_SEPARATOR . $moduleId);
    }


    private function removeModuleDir($path)
    {
        if (is_dir($path)) {
            $files = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::SKIP_DOTS),
                RecursiveIteratorIterator::CHILD_FIRST,
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
        $zip = new ZipArchive();
        $res = $zip->open($file);
        if ($res !== true) {
            return false;
        }
        $zip->extractTo($folder);
        $zip->close();

        return true;
    }

    private function checkRequirements($moduleId, $moduleZipFile)
    {
        $zip = new ZipArchive();
        $zip->open($moduleZipFile);
        if ($zip->locateName($moduleId . '/requirements.php')) {
            $requirementCheckResult = include('zip://' . $moduleZipFile . '#' . $moduleId . '/requirements.php');
            if (is_string($requirementCheckResult)) {
                $this->throwError($moduleId, $requirementCheckResult);
            }
        }
    }

    private function downloadModule($moduleId): string
    {
        $moduleInfo = $this->getModuleInfo($moduleId);

        /** @var Module $marketplaceModule */
        $marketplaceModule = Yii::$app->getModule('marketplace');

        // Check Module Folder exists
        $moduleDownloadFolder = Yii::getAlias($marketplaceModule->modulesDownloadPath);
        FileHelper::createDirectory($moduleDownloadFolder);


        // Download
        $downloadUrl = $moduleInfo['latestCompatibleVersion']['downloadUrl'];
        $downloadTargetFileName = $moduleDownloadFolder . DIRECTORY_SEPARATOR . basename($downloadUrl);
        try {
            $hashSha256 = $moduleInfo['latestCompatibleVersion']['downloadFileSha256'];
            $this->downloadFile($moduleId, $downloadTargetFileName, $downloadUrl, $hashSha256);
        } catch (\Exception $ex) {
            $this->throwError($moduleId, Yii::t('MarketplaceModule.base', 'Module download failed! (%error%)', ['%error%' => $ex->getMessage()]));
        }

        // Extract Package
        if (!file_exists($downloadTargetFileName)) {
            $this->throwError($moduleId, Yii::t('MarketplaceModule.base', 'Download of module failed!'));
        }

        return $downloadTargetFileName;
    }


    private function downloadFile(string $moduleId, $fileName, $url, $sha256 = null)
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
            $this->throwError($moduleId, 'Download failed.' . $e->getMessage());
        }

        if (!is_file($fileName)) {
            $this->throwError($moduleId, 'Download failed. Could not write file! ' . $fileName);
        }

        if (!empty($sha256) && hash_file('sha256', $fileName) !== $sha256) {
            $this->throwError($moduleId, 'File verification failed. Could not download file! ' . $fileName);
        }

        return true;
    }


    /**
     * Updates a given module
     *
     * @param $moduleId
     * @return void
     * @throws Exception
     * @throws InvalidConfigException
     * @throws ServerErrorHttpException
     * @throws ErrorException
     * @throws HttpException
     * @throws InvalidConfigException
     */
    public function update($moduleId)
    {
        $this->trigger(static::EVENT_BEFORE_UPDATE, new ModuleEvent(['module' => Yii::$app->moduleManager->getModule($moduleId)]));

        $moduleZipFile = $this->downloadModule($moduleId);
        $this->checkRequirements($moduleId, $moduleZipFile);

        // Temporary disable module if enabled
        if (Yii::$app->hasModule($moduleId)) {
            Yii::$app->setModule($moduleId, null);
        }

        Yii::$app->moduleManager->removeModule($moduleId, false);

        $this->install($moduleId);

        $updatedModule = Yii::$app->moduleManager->getModule($moduleId);
        $updatedModule->update();

        (new MarketplaceService())->refreshPendingModuleUpdateCount();

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
     * @param bool $cached
     * @return array of modules
     */
    public function getModules(bool $cached = true)
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
                'includeBetaVersions' => (bool)$module->settings->get('includeBetaUpdates'),
            ]);

            foreach ($module->moduleBlacklist as $blacklistedModuleId) {
                unset($this->_modules[$blacklistedModuleId]);
            }

            Yii::$app->cache->set('onlineModuleManager_modules', $this->_modules, Yii::$app->settings->get('cacheExpireTime'));
        }

        return $this->_modules;
    }

    public function getCategories(): array
    {
        return Yii::$app->cache->getOrSet('marketplace-categories', function () {
            $modules = $this->getModules();
            $categories = HumHubAPI::request('v1/modules/list-categories');

            $totalCount = 0;
            $withoutCategoryCount = 0;
            foreach ($modules as $module) {
                $onlineModule = new ModelModule($module);
                if (!$onlineModule->isMarketplaced()) {
                    continue;
                }

                $totalCount++;

                if (empty($module['categories'])) {
                    $withoutCategoryCount++;
                    continue;
                }

                foreach ($module['categories'] as $catIndex) {
                    if (isset($categories[$catIndex])) {
                        if (!isset($categories[$catIndex]['count'])) {
                            $categories[$catIndex]['count'] = 0;
                        }
                        $categories[$catIndex]['count']++;
                    }
                }
            }

            $names = [];
            $names[0] = Yii::t('MarketplaceModule.base', 'All modules') . ' (' . $totalCount . ')';

            foreach ($categories as $c => $category) {
                $names[$c] = $category['name'] . ' (' . ($category['count'] ?? '0') . ')';
            }

            if ($withoutCategoryCount > 0) {
                $names[-1] = Yii::t('MarketplaceModule.base', 'Without category') . ' (' . $withoutCategoryCount . ')';
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
            'id' => $moduleId, 'includeBetaVersions' => (bool)$module->settings->get('includeBetaUpdates'),
        ]);
    }

    /**
     * Get only not installed modules
     *
     * @return ModelModule[]
     */
    public function getNotInstalledModules(): array
    {
        $modules = [];

        foreach ($this->getModules() as $moduleId => $module) {
            $onlineModule = new ModelModule($module);
            if (!$onlineModule->isInstalled() && $onlineModule->isMarketplaced()) {
                $modules[$moduleId] = $onlineModule;
            }
        }

        return $modules;
    }

    /**
     * Get only installed modules
     *
     * @return ModelModule[]
     */
    public function getInstalledModules(): array
    {
        $modules = [];

        foreach ($this->getModules() as $moduleId => $module) {
            $onlineModule = new ModelModule($module);
            if ($onlineModule->isInstalled()) {
                $modules[$moduleId] = $onlineModule;
            }
        }

        return $modules;
    }

    /**
     * Get only purchased modules
     *
     * @param bool $cached
     * @return ModelModule[]
     */
    public function getPurchasedModules(bool $cached = true): array
    {
        $modules = $this->getModules($cached);

        foreach ($modules as $i => $module) {
            if (!isset($module['purchased']) || !$module['purchased']) {
                unset($modules[$i]);
            }
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

    /**
     * Get online module by ID
     *
     * @param string $id
     * @return ModelModule|null
     */
    public function getModule(string $id): ?ModelModule
    {
        $modules = $this->getModules();
        return isset($modules[$id]) ? new ModelModule($modules[$id]) : null;
    }

    /**
     * @throws ServerErrorHttpException
     */
    private function throwError(string $moduleId, string $errorMsg, string $displayedErrorMsg = null): void
    {
        Yii::error('Error installing or updating the "' . $moduleId . '" module: ' . $errorMsg, 'marketplace');
        throw new ServerErrorHttpException($displayedErrorMsg ?? $errorMsg);
    }

}
