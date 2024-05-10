<?php

/*
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\components;

use humhub\models\Setting;
use humhub\modules\activity\components\BaseActivity;
use humhub\modules\admin\jobs\DisableModuleJob;
use humhub\modules\admin\jobs\RemoveModuleJob;
use humhub\modules\content\models\ContentContainerSetting;
use humhub\modules\file\libs\FileHelper;
use humhub\modules\marketplace\models\Module as OnlineModelModule;
use humhub\modules\notification\components\BaseNotification;
use humhub\modules\queue\helpers\QueueHelper;
use humhub\services\MigrationService;
use Throwable;
use Yii;
use yii\base\InvalidConfigException;
use yii\db\StaleObjectException;
use yii\helpers\Json;
use yii\web\AssetBundle;

/**
 * Base Class for Modules / Extensions
 *
 * @property-read string $name
 * @property-read string $description
 * @property-read array $keywords
 * @property-read bool $isEnabled
 * @property SettingsManager $settings
 * @author luke
 */
class Module extends \yii\base\Module
{
    /**
     * @var array|null the loaded module.json info file
     */
    private ?array $_moduleInfo = null;

    /**
     * @var string The path for module resources (images, javascripts)
     * Also module related assets like README.md and module_image.png should be placed here.
     */
    public $resourcesPath = 'resources';

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        // Set settings component
        $this->set('settings', [
            'class' => SettingsManager::class,
            'moduleId' => $this->id,
        ]);
    }

    /**
     * Returns the module's name provided by module.json file
     *
     * @return string Name
     */
    public function getName()
    {
        return $this->getModuleInfo()['name'] ?? $this->id;
    }

    /**
     * Returns the module's description provided by module.json file
     *
     * @return string Description
     */
    public function getDescription()
    {
        return $this->getModuleInfo()['description'] ?? '';
    }

    /**
     * Returns the module's version number provided by module.json file
     *
     * @return string Version Number
     */
    public function getVersion()
    {
        return $this->getModuleInfo()['version'] ?? '1.0';
    }

    /**
     * Returns image url for this module
     * Place your modules image in <resourcesPath>/module_image.png
     *
     * @return String Image Url
     */
    public function getImage()
    {
        $url = $this->getPublishedUrl('/module_image.png');

        if ($url == null) {
            $url = Yii::getAlias("@web-static/img/default_module.jpg");
        }

        return $url;
    }

    /**
     * Returns module's keywords provided by module.json file
     *
     * @return array List of keywords
     */
    public function getKeywords(): array
    {
        return $this->getModuleInfo()['keywords'] ?? [];
    }

    /**
     * Returns the url of an asset file and publishes all module assets if
     * the file is not published yet.
     *
     * @param string $relativePath relative file path e.g. /module_image.jpg
     * @return string
     */
    public function getPublishedUrl($relativePath)
    {
        $path = $this->getAssetPath();

        // If the file has not been published yet we publish the module assets
        if (!$this->isPublished($relativePath)) {
            $this->publishAssets();
        }

        // If its still not published the file does not exist
        if ($this->isPublished($relativePath)) {
            return Yii::$app->assetManager->getPublishedUrl($path) . $relativePath;
        }
    }

    /**
     * Checks if a specific asset file has already been published
     * @param string $relativePath
     * @return string
     */
    public function isPublished($relativePath)
    {
        $path = $this->getAssetPath();
        $publishedPath = Yii::$app->assetManager->getPublishedPath($path);

        return $publishedPath !== false && is_file($publishedPath . $relativePath);
    }

    /**
     * Get Assets Url
     *
     * @return string Image Url
     */
    public function getAssetsUrl()
    {
        if (($published = $this->publishAssets()) != null) {
            return $published[1];
        }
    }

    /**
     * Publishes the basePath/resourcesPath (assets) module directory if existing.
     * @param bool $all whether or not to publish sub assets within the `assets` directory
     * @return array
     */
    public function publishAssets($all = false)
    {
        /** @var $assetBundle AssetBundle */
        /** @var $manager AssetManager */

        if ($all) {
            foreach ($this->getAssetClasses() as $assetClass) {
                $assetBundle = new $assetClass();
                $manager = Yii::$app->getAssetManager();
                $manager->forcePublish($assetBundle);
            }
        }

        if ($this->hasAssets()) {
            return Yii::$app->assetManager->publish($this->getAssetPath(), ['forceCopy' => true]);
        }
    }

    /**
     * Determines whether or not this module has an asset directory.
     * @return bool
     */
    private function hasAssets()
    {
        $path = $this->getAssetPath();
        $path = Yii::getAlias($path);

        return is_string($path) && is_dir($path);
    }

    public function getAssetPath()
    {
        return $this->getBasePath() . '/' . $this->resourcesPath;
    }

    /**
     * Check this module is enabled
     *
     * @return bool
     */
    public function getIsEnabled(): bool
    {
        return
            Yii::$app->hasModule($this->id)
            && !QueueHelper::isQueued(new DisableModuleJob(['moduleId' => $this->id]))
            && !QueueHelper::isQueued(new RemoveModuleJob(['moduleId' => $this->id]));
    }

    /**
     * @see          static::getIsEnabled()
     * @deprecated since 1.16; use static::getIsEnabled() instead.
     * @noinspection PhpUnused
     */
    public function getIsActivated(): bool
    {
        return $this->getIsEnabled();
    }

    /**
     * Enables this module
     *
     * @return bool|null Result of migration or null if beforeEnable() returned false (since v1.16)
     * @throws InvalidConfigException
     */
    public function enable()
    {
        Yii::$app->moduleManager->enable($this);
        $result = $this->getMigrationService()->migrateUp();

        if ($result === false) {
            Yii::$app->moduleManager->disable($this);
            Yii::error('Could not enable module. Database Migration failed! See previous error for result.', $this->id);
            return false;
        }

        return $result;
    }

    /**
     * Disables a module
     *
     * This should delete all data created by this module.
     * When overriding this method, make sure to invoke call `parent::disable()` **AFTER** your implementation as
     *
     * ```php
     * public function disable()
     * {
     *     // custom disable logic
     *     parent::disable();
     * }
     * ```
     *
     * @return bool|null Result uninstall-migration or null if beforeDisable() returned false (since v1.16)
     * @throws InvalidConfigException
     * @throws StaleObjectException
     * @throws Throwable
     */
    public function disable()
    {
        try {
            $result = $this->getMigrationService()->uninstall();
            ContentContainerSetting::deleteAll(['module_id' => $this->id]);
            Setting::deleteAll(['module_id' => $this->id]);
        } catch (Throwable $ex) {
            Yii::error($ex, $this->id);
            $result = false;
        }

        Yii::$app->moduleManager->disable($this);

        return $result;
    }

    /**
     * Execute all not applied module migrations
     * @deprecated since v1.16; use static::getMigrationService()->migrateUp()
     */
    public function migrate()
    {
        return $this->getMigrationService()->migrateUp();
    }

    public function getMigrationService(): MigrationService
    {
        return new MigrationService($this);
    }

    /**
     * Reads module.json which contains basic module information and
     * returns it as array
     *
     * @return array module.json content
     */
    protected function getModuleInfo(): array
    {
        if ($this->_moduleInfo === null) {
            $configPath = $this->getBasePath() . DIRECTORY_SEPARATOR . 'module.json';
            $this->_moduleInfo = file_exists($configPath)
                ? Json::decode(file_get_contents($configPath))
                : ['id' => $this->id];
        }

        return $this->_moduleInfo;
    }

    /**
     * This method is called after an update is performed.
     * You may extend it with your own update process.
     */
    public function update()
    {
        if ($this->beforeUpdate() !== false) {
            $this->migrate();
            $this->afterUpdate();
        }
    }

    /**
     * Called right before the module is updated.
     *
     * The update will cancel if this function does return false;
     *
     * @return bool
     * @deprecated
     *
     */
    public function beforeUpdate()
    {
        return true;
    }

    /**
     * Called right after the module update.
     *
     * @deprecated
     */
    public function afterUpdate()
    {
    }

    /**
     * URL to the module's configuration action
     *
     * @return string the configuration url
     */
    public function getConfigUrl()
    {
        return "";
    }

    /**
     * Returns a list of permission objects this module provides.
     *
     * If a content container is provided, the method should only return applicable permissions for the given container.
     * This function should also make sure the module is installed on the given container in case the permission
     * only affects installed features.
     *
     * @param \humhub\modules\content\components\ContentContainerActiveRecord $contentContainer optional contentcontainer
     * @return array list of permissions
     * @since 0.21
     */
    public function getPermissions($contentContainer = null)
    {
        return [];
    }

    /**
     * Returns a list of notification classes this module provides.
     *
     * @return array list of notification classes
     * @since 1.1
     */
    public function getNotifications()
    {
        $class = get_class($this);
        if (($pos = strrpos($class, '\\')) !== false) {
            $notificationNamespace = substr($class, 0, $pos) . '\\notifications';
        } else {
            $notificationNamespace = '';
        }

        $notifications = [];
        $notificationDirectory = $this->getBasePath() . DIRECTORY_SEPARATOR . 'notifications';
        if (is_dir($notificationDirectory)) {
            foreach (FileHelper::findFiles($notificationDirectory, ['recursive' => false,]) as $file) {
                $notificationClass = $notificationNamespace . '\\' . basename($file, '.php');
                if (is_subclass_of($notificationClass, BaseNotification::class)) {
                    $notifications[] = $notificationClass;
                }
            }
        }

        return $notifications;
    }

    /**
     * Determines whether the module has notification classes or not
     *
     * @return bool has notifications
     * @since 1.2
     */
    public function hasNotifications()
    {
        return !empty($this->getNotifications());
    }

    /**
     * Returns a list of activity class names this modules provides.
     *
     * @return array list of activity class names
     * @since 1.2
     */
    public function getActivityClasses()
    {
        $class = get_class($this);
        if (($pos = strrpos($class, '\\')) !== false) {
            $activityNamespace = substr($class, 0, $pos) . '\\activities';
        } else {
            $activityNamespace = '';
        }

        $activities = [];
        $activityDirectory = $this->getBasePath() . DIRECTORY_SEPARATOR . 'activities';
        if (is_dir($activityDirectory)) {
            foreach (FileHelper::findFiles($activityDirectory, ['recursive' => false,]) as $file) {
                $activityClass = $activityNamespace . '\\' . basename($file, '.php');
                if (is_subclass_of($activityClass, BaseActivity::class)) {
                    $activities[] = $activityClass;
                }
            }
        }

        return $activities;
    }

    /**
     * Returns a list of asset class names this modules provides.
     *
     * @return array list of asset class names
     * @since 1.2.8
     */
    public function getAssetClasses()
    {
        $class = get_class($this);
        if (($pos = strrpos($class, '\\')) !== false) {
            $assetNamespace = substr($class, 0, $pos) . '\\assets';
        } else {
            $assetNamespace = '';
        }

        $assets = [];
        $assetDirectory = $this->getBasePath() . DIRECTORY_SEPARATOR . 'assets';
        if (is_dir($assetDirectory)) {
            foreach (FileHelper::findFiles($assetDirectory, ['recursive' => false,]) as $file) {
                $assetClass = $assetNamespace . '\\' . basename($file, '.php');
                if (is_subclass_of($assetClass, AssetBundle::class)) {
                    $assets[] = $assetClass;
                }
            }
        }

        return $assets;
    }

    public function getOnlineModule(): ?OnlineModelModule
    {
        /* @var \humhub\modules\marketplace\Module $marketplaceModule */
        $marketplaceModule = Yii::$app->getModule('marketplace');

        if (!$marketplaceModule->enabled) {
            return null;
        }

        return $marketplaceModule->onlineModuleManager->getModule($this->id);
    }
}
