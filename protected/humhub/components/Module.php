<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\components;

use humhub\modules\file\libs\FileHelper;
use humhub\modules\notification\components\BaseNotification;
use Yii;
use yii\helpers\Json;

/**
 * Base Class for Modules / Extensions
 *
 * @property SettingsManager $settings
 * @author luke
 */
class Module extends \yii\base\Module
{

    /**
     * @var array the loaded module.json info file
     */
    private $_moduleInfo = null;

    /**
     * @var string The path for module resources (images, javascripts)
     * Also module related assets like README.md and module_image.png should be placed here.
     */
    public $resourcesPath = 'assets';

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        // Set settings component
        $this->set('settings', [
            'class' => SettingsManager::className(),
            'moduleId' => $this->id
        ]);
    }

    /**
     * Returns modules name provided by module.json file
     *
     * @return string Name
     */
    public function getName()
    {
        $info = $this->getModuleInfo();

        if ($info['name']) {
            return $info['name'];
        }

        return $this->id;
    }

    /**
     * Returns modules description provided by module.json file
     *
     * @return string Description
     */
    public function getDescription()
    {
        $info = $this->getModuleInfo();

        if ($info['description']) {
            return $info['description'];
        }

        return "";
    }

    /**
     * Returns modules version number provided by module.json file
     *
     * @return string Version Number
     */
    public function getVersion()
    {
        $info = $this->getModuleInfo();

        if ($info['version']) {
            return $info['version'];
        }

        return "1.0";
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
     * @return array
     */
    public function publishAssets()
    {
        if ($this->hasAssets()) {
            return Yii::$app->assetManager->publish($this->getAssetPath(), ['forceCopy' => true]);
        }
    }

    /**
     * Determines whether or not this module has an asset directory.
     * @return boolean
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
     * Enables this module
     *
     * @return boolean
     */
    public function enable()
    {
        Yii::$app->moduleManager->enable($this);
        $this->migrate();

        return true;
    }

    /**
     * Disables a module
     *
     * This should delete all data created by this module.
     * When override this method make sure to invoke call `parent::disable()` **AFTER** your implementation as
     *
     * ```php
     * public function disable()
     * {
     *     // custom disable logic
     *     parent::disable();
     * }
     * ```
     */
    public function disable()
    {
        /**
         * Remove database tables
         */
        $migrationPath = $this->getBasePath() . '/migrations';
        $uninstallMigration = $migrationPath . '/uninstall.php';
        if (file_exists($uninstallMigration)) {

            /**
             * Execute Uninstall Migration
             */
            ob_start();
            require_once($uninstallMigration);
            $migration = new \uninstall;
            try {
                $migration->up();
            } catch (\yii\db\Exception $ex) {
                Yii::error($ex);
            }
            ob_get_clean();

            /**
             * Delete all Migration Table Entries
             */
            $migrations = opendir($migrationPath);
            while (false !== ($migration = readdir($migrations))) {
                if ($migration == '.' || $migration == '..' || $migration == 'uninstall.php') {
                    continue;
                }
                Yii::$app->db->createCommand()->delete('migration', ['version' => str_replace('.php', '', $migration)])->execute();
            }
        }

        foreach (\humhub\modules\content\models\ContentContainerSetting::findAll(['module_id' => $this->id]) as $containerSetting) {
            $containerSetting->delete();
        }

        foreach (\humhub\models\Setting::findAll(['module_id' => $this->id]) as $containerSetting) {
            $containerSetting->delete();
        }

        foreach (\humhub\modules\user\models\Module::findAll(['module_id' => $this->id]) as $userModule) {
            $userModule->delete();
        }

        foreach (\humhub\modules\space\models\Module::findAll(['module_id' => $this->id]) as $spaceModule) {
            $spaceModule->delete();
        }

        Yii::$app->moduleManager->disable($this);
    }

    /**
     * Execute all not applied module migrations
     */
    public function migrate()
    {
        $migrationPath = $this->basePath . '/migrations';
        if (is_dir($migrationPath)) {
            \humhub\commands\MigrateController::webMigrateUp($migrationPath);
        }
    }

    /**
     * Reads module.json which contains basic module information and
     * returns it as array
     *
     * @return array module.json content
     */
    protected function getModuleInfo()
    {
        if ($this->_moduleInfo != null) {
            return $this->_moduleInfo;
        }

        $moduleJson = file_get_contents($this->getBasePath() . DIRECTORY_SEPARATOR . 'module.json');

        return Json::decode($moduleJson);
    }

    /**
     * This method is called after an update is performed.
     * You may extend it with your own update process.
     */
    public function update()
    {
        $this->migrate();
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
     * If a ContentContainer is provided, the method should only return applicable permissions in content container context.
     *
     * @since 0.21
     * @param \humhub\modules\content\components\ContentContainerActiveRecord $contentContainer optional contentcontainer
     * @return array list of permissions
     */
    public function getPermissions($contentContainer = null)
    {
        return [];
    }

    /**
     * Returns a list of notification classes this module provides.
     *
     * @since 1.1
     * @return array list of notification classes
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
                if(is_subclass_of($notificationClass, BaseNotification::class)) {
                    $notifications[] = $notificationClass;
                }
            }
        }

        return $notifications;
    }

    /**
     * Determines whether the module has notification classes or not
     *
     * @since 1.2
     * @return boolean has notifications
     */
    public function hasNotifications()
    {
        return !empty($this->getNotifications());
    }

    /**
     * Returns a list of activity class names this modules provides.
     *
     * @since 1.2
     * @return array list of activity class names
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
                $activities[] = $activityNamespace . '\\' . basename($file, '.php');
            }
        }

        return $activities;
    }

}
