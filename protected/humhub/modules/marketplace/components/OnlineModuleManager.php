<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\marketplace\components;

use humhub\components\ModuleEvent;
use humhub\models\ModuleEnabled;
use humhub\modules\admin\libs\HumHubAPI;
use humhub\services\ModuleDiscoveryService;
use humhub\services\ModuleService;
use humhub\modules\marketplace\models\Module as ModelModule;
use humhub\modules\marketplace\Module;
use humhub\modules\marketplace\services\MarketplaceService;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Yii;
use yii\base\Application;
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

    /**
     * @since 1.20
     */
    public const CACHE_KEY_MODULES = 'onlineModuleManager_modules';

    /**
     * @since 1.20
     */
    public const CACHE_KEY_CATEGORIES = 'marketplace-category-list';

    /**
     * How long a failure to reach humhub.com (an empty answer for the module list or the
     * category list) is cached, so repeated calls during an outage do not each retry the
     * request.
     *
     * @since 1.20
     */
    public const FAILURE_CACHE_TTL = 120;

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

        // Reset the OPcache so the updated module files are recompiled on the following requests.
        // This is deferred to the end of the request: in worker runtimes (e.g. FrankenPHP) opcache_reset()
        // restarts the worker, which would otherwise interrupt the still running update (cache flush,
        // registration and database migrations) and leave the module in a half-updated state.
        if (function_exists('opcache_reset')) {
            Yii::$app->on(Application::EVENT_AFTER_REQUEST, static function (): void {
                @opcache_reset();
            });
        }

        Yii::$app->moduleManager->flushCache();
        Yii::$app->moduleManager->register($modulesPath . DIRECTORY_SEPARATOR . $moduleId);
        $this->refreshMarketplaceLastChange();
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
        $downloadTargetFileName = $moduleDownloadFolder . DIRECTORY_SEPARATOR . basename((string) $downloadUrl);
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
        $this->trigger(static::EVENT_BEFORE_UPDATE, new ModuleEvent(['module' => Yii::$app->moduleManager->getModule($moduleId, false)]));

        $moduleZipFile = $this->downloadModule($moduleId);
        $this->checkRequirements($moduleId, $moduleZipFile);

        // Temporary disable module if enabled
        if (Yii::$app->hasModule($moduleId)) {
            Yii::$app->setModule($moduleId, null);
        }

        $moduleToRemove = Yii::$app->moduleManager->getModule($moduleId, false);
        if ($moduleToRemove !== null) {
            (new ModuleService($moduleToRemove))->remove(false);
        }

        $this->install($moduleId);

        $updatedModule = Yii::$app->moduleManager->getModule($moduleId);
        $updatedModule->update();

        (new MarketplaceService())->refreshPendingModuleUpdateCount();

        $moduleEnabled = ModuleEnabled::findOne(['module_id' => $updatedModule->id]);
        if ($moduleEnabled) {
            $moduleEnabled->version = $updatedModule->version;
            $moduleEnabled->save();
        }

        $this->refreshMarketplaceLastChange();

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
     * A cached `[]` (an empty answer from humhub.com, kept for {@see self::FAILURE_CACHE_TTL}
     * seconds, see below) is a cache hit like any other and is returned without a request; only
     * an actual cache miss (nothing stored yet, or `$cached` is `false`) triggers one.
     *
     * @param bool $cached false bypasses the cache and always asks humhub.com
     * @return array of modules
     */
    public function getModules(bool $cached = true)
    {
        if (!$cached) {
            $this->_modules = null;
            Yii::$app->cache->delete(self::CACHE_KEY_MODULES);
        }

        if ($this->_modules !== null) {
            return $this->_modules;
        }

        /** @var Module $module */
        $module = Yii::$app->getModule('marketplace');

        $this->_modules = Yii::$app->cache->get(self::CACHE_KEY_MODULES);
        if ($this->_modules === null || !is_array($this->_modules)) {
            $this->_modules = HumHubAPI::request('v1/modules/list', [
                'includeBetaVersions' => (bool)$module->settings->get('includeBetaUpdates'),
            ]);

            foreach ($module->moduleBlacklist as $blacklistedModuleId) {
                unset($this->_modules[$blacklistedModuleId]);
            }

            if (!empty($this->_modules)) {
                Yii::$app->cache->set(self::CACHE_KEY_MODULES, $this->_modules, Yii::$app->settings->get('cacheExpireTime'));
            } else {
                // Negative cache: humhub.com answered nothing (unreachable, or a truly empty
                // catalogue). Cached briefly rather than not at all, so a page that builds
                // several widgets, or repeated requests during an outage, do not each retry it.
                $this->_modules = [];
                Yii::$app->cache->set(self::CACHE_KEY_MODULES, $this->_modules, self::FAILURE_CACHE_TTL);
            }
        }

        if (!(bool)$module->settings->get('includeCommunityModules', false)) {
            $installed = ModuleDiscoveryService::findInstalledModules();
            foreach ($this->_modules as $id => $info) {
                if (!empty($info['isCommunity'])
                    && !Yii::$app->moduleManager->hasModule($id)
                    && !array_key_exists($id, $installed)) {
                    unset($this->_modules[$id]);
                }
            }
        }

        return $this->_modules;
    }

    /**
     * The categories of the marketplace with the number of listed modules in each, plus the
     * number of listed modules without a category. A module is counted under the same rule the
     * list uses (installed, or not installed but marketplaced), so a category's count matches
     * what filtering by it returns.
     *
     * A failure (no modules, or no categories) is cached too, as a marker, for
     * {@see self::FAILURE_CACHE_TTL} seconds, so it keeps answering `null` without a new
     * request during that window; a successful answer is cached for `cacheExpireTime` as before.
     *
     * @return array{categories: array<array{id: int, name: string, count: int}>, uncategorized: int}|null
     *         `null` while humhub.com cannot be reached
     * @since 1.20
     */
    public function getCategoryList(): ?array
    {
        $cached = Yii::$app->cache->get(self::CACHE_KEY_CATEGORIES);
        if (is_array($cached)) {
            return ($cached['failed'] ?? false) ? null : $cached;
        }

        $modules = $this->getModules();
        if (empty($modules)) {
            return $this->cacheCategoryListFailure();
        }

        $categories = HumHubAPI::request('v1/modules/list-categories');
        if (empty($categories) || !is_array($categories)) {
            return $this->cacheCategoryListFailure();
        }

        $counts = [];
        $uncategorized = 0;
        foreach ($modules as $module) {
            $onlineModule = new ModelModule($module);
            if (!$onlineModule->isInstalled() && !$onlineModule->isMarketplaced()) {
                continue;
            }
            if (empty($module['categories'])) {
                $uncategorized++;
                continue;
            }
            foreach ((array)$module['categories'] as $categoryId) {
                $counts[$categoryId] = ($counts[$categoryId] ?? 0) + 1;
            }
        }

        $list = [];
        foreach ($categories as $categoryId => $category) {
            $list[] = [
                'id' => (int)$categoryId,
                'name' => (string)($category['name'] ?? ''),
                'count' => $counts[$categoryId] ?? 0,
            ];
        }

        $data = ['categories' => $list, 'uncategorized' => $uncategorized];
        Yii::$app->cache->set(self::CACHE_KEY_CATEGORIES, $data, (int)Yii::$app->settings->get('cacheExpireTime'));

        return $data;
    }

    /**
     * Caches the failure marker read back by {@see self::getCategoryList()}.
     */
    private function cacheCategoryListFailure(): ?array
    {
        Yii::$app->cache->set(self::CACHE_KEY_CATEGORIES, ['failed' => true], self::FAILURE_CACHE_TTL);

        return null;
    }


    public function getModuleUpdates($cached = true)
    {
        $updates = [];

        foreach ($this->getModules($cached) as $moduleId => $moduleInfo) {
            if (!isset($moduleInfo['latestCompatibleVersion'])) {
                continue;
            }

            $installedVersion = $this->getInstalledVersion($moduleId);
            if ($installedVersion === null) {
                continue;
            }

            if (version_compare($moduleInfo['latestCompatibleVersion'], $installedVersion, 'gt')) {
                $updates[$moduleId] = $moduleInfo;
            }
        }

        return $updates;
    }

    /**
     * Returns the installed version of a module.
     *
     * Prefers the loaded module instance for accuracy; falls back to reading module.json
     * from the filesystem when the module could not be loaded (e.g. during upgrades when
     * the module config references a removed core class).
     */
    private function getInstalledVersion(string $moduleId): ?string
    {
        if (Yii::$app->moduleManager->hasModule($moduleId)) {
            $module = Yii::$app->moduleManager->getModule($moduleId, false);
            if ($module !== null) {
                return $module->getVersion();
            }
        }

        return ModuleDiscoveryService::findInstalledVersion($moduleId);
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
    private function throwError(string $moduleId, string $errorMsg, ?string $displayedErrorMsg = null): void
    {
        Yii::error('Error installing or updating the "' . $moduleId . '" module: ' . $errorMsg, 'marketplace');
        throw new ServerErrorHttpException($displayedErrorMsg ?? $errorMsg);
    }

    public function refreshMarketplaceLastChange(): void
    {
        Yii::$app->settings->set('marketplaceLastChange', time());
    }

}
