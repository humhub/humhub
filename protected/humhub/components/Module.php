<?php

/*
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\components;

use humhub\components\assets\AssetManager;
use humhub\helpers\ThemeHelper;
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
use humhub\services\ModuleService;
use Throwable;
use Yii;
use yii\base\InvalidConfigException;
use yii\db\StaleObjectException;
use yii\helpers\Json;
use yii\helpers\Url;
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
            $url = Yii::$app->assetManager->getPublishedUrl('@humhub/resources') . '/img/default_module.jpg';
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
     * Returns the url of a published module asset file (publishing the module's
     * assets on demand), or `null` if the module does not ship the given file.
     *
     * Whether the module ships the file is checked against its own - always
     * local - resources directory. Whether the assets are already published is
     * handled by {@see \humhub\components\assets\AssetManager::publish()}, whose
     * result is cached and invalidated on cache clear. This avoids probing the
     * published location, which may live on a remote (e.g. S3) mount, on every
     * call.
     *
     * @param string $relativePath relative file path e.g. /module_image.png
     * @return string|null
     */
    public function getPublishedUrl($relativePath)
    {
        if (!$this->isPublished($relativePath)) {
            return null;
        }

        $published = Yii::$app->assetManager->publish($this->getAssetPath());

        return isset($published[1]) ? $published[1] . $relativePath : null;
    }

    /**
     * Checks whether the module ships the given asset file in its (local)
     * resources directory. The published copy is created on demand by
     * {@see getPublishedUrl()}, so this deliberately checks the source and not
     * the published location, which may live on a remote (e.g. S3) mount.
     *
     * @param string $relativePath relative file path e.g. /module_image.png
     * @return bool
     */
    public function isPublished($relativePath)
    {
        return $this->hasAssets()
            && is_file(Yii::getAlias($this->getAssetPath()) . $relativePath);
    }

    /**
     * Get Assets Url
     *
     * @return string Image Url
     */
    public function getAssetsUrl(): string
    {
        $published = $this->publishAssets();
        return $published && isset($published[1]) ? Url::to($published[1]) : '';
    }

    /**
     * Publishes the basePath/resourcesPath (assets) module directory if existing.
     * @param bool $all whether or not to publish sub assets within the `assets` directory
     * @return array|null
     */
    public function publishAssets(bool $all = false): ?array
    {
        /** @var $assetBundle AssetBundle */
        /** @var $manager AssetManager */

        if ($all) {
            foreach ($this->getAssetClasses() as $assetClass) {
                $assetBundle = new $assetClass();
                $assetBundle->publishOptions['forceCopy'] = true;
                $assetBundle->publish(Yii::$app->assetManager);
            }
        }

        return $this->hasAssets()
            ? Yii::$app->assetManager->publish($this->getAssetPath(), ['forceCopy' => true])
            : null;
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
     * Enables this module.
     *
     * Override this method to run custom logic when the module is enabled. Call
     * `parent::enable()` **before** your custom code so the module is already
     * registered and active when your code runs:
     *
     * ```php
     * public function enable()
     * {
     *     parent::enable();
     *     // custom enable logic here
     * }
     * ```
     *
     * The base implementation delegates the registration step to {@see ModuleService::enable()}
     * and then runs pending database migrations via {@see MigrationService::migrateUp()}.
     * If migrations fail, the registration is rolled back automatically.
     *
     * @return bool|null migration result, or false if migrations failed
     * @throws InvalidConfigException
     */
    public function enable()
    {
        $this->getModuleService()->enable();
        $result = $this->getMigrationService()->migrateUp();

        if ($result === false) {
            $this->getModuleService()->disable();
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

        $this->getModuleService()->disable();

        return $result;
    }

    public function getMigrationService(): MigrationService
    {
        return new MigrationService($this);
    }

    /**
     * @since 1.19
     */
    public function getModuleService(): ModuleService
    {
        return new ModuleService($this);
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
        if ($this->isEnabled) {
            $this->getMigrationService()->migrateUp();

            // Check if current theme (parent) is located in this module
            foreach (array_merge([Yii::$app->view->theme], Yii::$app->view->theme->getParents()) as $theme) {
                if (str_starts_with((string) $theme->getBasePath(), $this->getBasePath())) {
                    try {
                        ThemeHelper::buildCss();
                        break;
                    } catch (\Exception $e) {
                        Yii::error('Could not build Theme CSS after Module Update: ' . $e->getMessage());
                    }
                }
            }
        }
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
        $class = static::class;
        if (($pos = strrpos($class, '\\')) !== false) {
            $notificationNamespace = substr($class, 0, $pos) . '\\notifications';
        } else {
            $notificationNamespace = '';
        }

        $notifications = [];
        $notificationDirectory = $this->getBasePath() . DIRECTORY_SEPARATOR . 'notifications';
        if (is_dir($notificationDirectory)) {
            foreach (FileHelper::findFiles($notificationDirectory, ['recursive' => false,]) as $file) {
                $notificationClass = $notificationNamespace . '\\' . basename((string) $file, '.php');
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
        $class = static::class;
        if (($pos = strrpos($class, '\\')) !== false) {
            $activityNamespace = substr($class, 0, $pos) . '\\activities';
        } else {
            $activityNamespace = '';
        }

        $activities = [];
        $activityDirectory = $this->getBasePath() . DIRECTORY_SEPARATOR . 'activities';
        if (is_dir($activityDirectory)) {
            foreach (FileHelper::findFiles($activityDirectory, ['recursive' => false,]) as $file) {
                $activityClass = $activityNamespace . '\\' . basename((string) $file, '.php');
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
        $class = static::class;
        if (($pos = strrpos($class, '\\')) !== false) {
            $assetNamespace = substr($class, 0, $pos) . '\\assets';
        } else {
            $assetNamespace = '';
        }

        $assets = [];
        $assetDirectory = $this->getBasePath() . DIRECTORY_SEPARATOR . 'assets';
        if (is_dir($assetDirectory)) {
            foreach (FileHelper::findFiles($assetDirectory, ['recursive' => false,]) as $file) {
                $assetClass = $assetNamespace . '\\' . basename((string) $file, '.php');
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
