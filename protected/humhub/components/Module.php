<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\components;

use Yii;
use humhub\models\ModuleEnabled;
use yii\base\Exception;

/**
 * Base Class for Modules / Extensions
 *
 * @author luke
 */
class Module extends \yii\base\Module
{

    /**
     * Loaded Module JSON File
     *
     * @var Array
     */
    private $_moduleInfo = null;

    /**
     * Config Route
     */
    public $configRoute = null;

    /**
     * The path for module resources (images, javascripts)
     * Also module related assets like README.md and module_image.png should be placed here.
     *
     * @var type
     */
    public $resourcesPath = 'assets';

    /**
     * Returns modules name provided by module.json file
     *
     * @return string Description
     */
    public function getName()
    {
        $info = $this->getModuleInfo();

        if ($info['name']) {
            return $info['name'];
        }

        return $this->getId();
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
     * Place your modules image in assets/module_image.png
     *
     * @return String Image Url
     */
    public function getImage()
    {
        $moduleImageFile = $this->getBasePath() . '/' . $this->resourcesPath . '/module_image.png';

        if (is_file($moduleImageFile)) {
            $published = $assetManager = Yii::$app->assetManager->publish($moduleImageFile);
            return $published[1];
        }

        return Yii::getAlias("@web/img/default_module.jpg");
    }

    /**
     * Enables this module
     * It will be available on the next request.
     *
     * @return boolean
     */
    public function enable()
    {
        if (!Yii::$app->hasModule($this->id)) {

            $moduleEnabled = ModuleEnabled::findOne(['module_id' => $this->id]);
            if ($moduleEnabled == null) {
                $moduleEnabled = new ModuleEnabled();
                $moduleEnabled->module_id = $this->id;
                $moduleEnabled->save();
            }

            $this->migrate();
            return true;
        }

        return false;
    }

    /**
     * Disables a module
     * 
     * This should delete all data created by this module.
     * When override this method make sure to invoke the parent implementation AFTER your implementation.
     */
    public function disable()
    {

        // Seems not enabled
        if (!Yii::$app->hasModule($this->id)) {
            return false;
        }

        // Disable module in database
        $moduleEnabled = ModuleEnabled::findOne(['module_id' => $this->id]);
        if ($moduleEnabled != null) {
            $moduleEnabled->delete();
        }

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
                ;
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

        /*
          HSetting::model()->deleteAllByAttributes(array('module_id' => $this->getId()));
          SpaceSetting::model()->deleteAllByAttributes(array('module_id' => $this->getId()));
          UserSetting::model()->deleteAllByAttributes(array('module_id' => $this->getId()));

          // Delete also records with disabled state from SpaceApplicationModule Table
          foreach (SpaceApplicationModule::model()->findAllByAttributes(array('module_id' => $this->getId())) as $sam) {
          $sam->delete();
          }

          // Delete also records with disabled state from UserApplicationModule Table
          foreach (UserApplicationModule::model()->findAllByAttributes(array('module_id' => $this->getId())) as $uam) {
          $uam->delete();
          }

          ModuleManager::flushCache();
         */
        return true;
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
     * Reads module.json which contains basic module informations and
     * returns it as array
     *
     * @return Array module.json content
     */
    protected function getModuleInfo()
    {
        if ($this->_moduleInfo != null) {
            return $this->_moduleInfo;
        }

        $moduleJson = file_get_contents($this->getBasePath() . DIRECTORY_SEPARATOR . 'module.json');
        return \yii\helpers\Json::decode($moduleJson);
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

}
