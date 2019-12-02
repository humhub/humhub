<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\marketplace\components;

use humhub\components\ModuleEvent;
use humhub\modules\admin\libs\HumHubAPI;
use humhub\modules\marketplace\Module;
use Yii;
use yii\base\Component;
use yii\web\HttpException;
use yii\base\Exception;
use yii\helpers\FileHelper;
use humhub\libs\CURLHelper;
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
        $modulePath = Yii::getAlias($marketplaceModule->modulesPath);

        if (!is_writable($modulePath)) {
            throw new Exception(Yii::t('MarketplaceModule.base', 'Module directory %modulePath% is not writeable!', ['%modulePath%' => $modulePath]));
        }

        $moduleInfo = $this->getModuleInfo($moduleId);

        if (!isset($moduleInfo['latestCompatibleVersion'])) {
            throw new Exception(Yii::t('MarketplaceModule.base', 'No compatible module version found!'));
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

            FileHelper::removeDirectory($moduleDir);
        }

        // Check Module Folder exists
        $moduleDownloadFolder = Yii::getAlias('@runtime/module_downloads');
        FileHelper::createDirectory($moduleDownloadFolder);

        $version = $moduleInfo['latestCompatibleVersion'];

        // Download
        $downloadUrl = $version['downloadUrl'];
        $downloadTargetFileName = $moduleDownloadFolder . DIRECTORY_SEPARATOR . basename($downloadUrl);
        try {
            $http = new \Zend\Http\Client($downloadUrl, [
                'adapter' => '\Zend\Http\Client\Adapter\Curl',
                'curloptions' => CURLHelper::getOptions(),
                'timeout' => 30
            ]);

            $response = $http->send();

            file_put_contents($downloadTargetFileName, $response->getBody());
        } catch (Exception $ex) {
            throw new HttpException('500', Yii::t('MarketplaceModule.base', 'Module download failed! (%error%)', ['%error%' => $ex->getMessage()]));
        }

        // Extract Package
        if (file_exists($downloadTargetFileName)) {
            $zip = new ZipArchive;
            $res = $zip->open($downloadTargetFileName);
            if ($res === true) {
                $zip->extractTo($modulePath);
                $zip->close();
            } else {
                throw new HttpException('500', Yii::t('MarketplaceModule.base', 'Could not extract module!'));
            }
        } else {
            throw new HttpException('500', Yii::t('MarketplaceModule.base', 'Download of module failed!'));
        }

        Yii::$app->moduleManager->flushCache();
        Yii::$app->moduleManager->register($modulePath . DIRECTORY_SEPARATOR . $moduleId);
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


    public function getModuleUpdates()
    {
        $updates = [];

        foreach ($this->getModules() as $moduleId => $moduleInfo) {

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
     */
    public function getModuleInfo($moduleId)
    {
        return HumHubAPI::request('v1/modules/info', ['id' => $moduleId]);
    }

}
