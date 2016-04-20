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
     * @var Array the loaded module.json info file
     */
    private $_moduleInfo = null;

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
     * @return string Name
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
            return $this->getAssetsUrl() . '/module_image.png';
        }

        return Yii::getAlias("@web/img/default_module.jpg");
    }

    /**
     * Get Assets Url
     *
     * @return String Image Url
     */
    public function getAssetsUrl()
    {
        $published = Yii::$app->assetManager->publish($this->getBasePath() . '/' . $this->resourcesPath);
        return $published[1];
    }

    /**
     * Enables this module
     * It will be available on the next request.
     *
     * @return boolean
     */
    public function enable()
    {
        $moduleEnabled = ModuleEnabled::findOne(['module_id' => $this->id]);
        if ($moduleEnabled == null) {
            $moduleEnabled = new ModuleEnabled();
            $moduleEnabled->module_id = $this->id;
            $moduleEnabled->save();
        }

        $this->migrate();
        return true;
    }

    /**
     * Disables a module
     * 
     * This should delete all data created by this module.
     * When override this method make sure to invoke the parent implementation AFTER your implementation.
     */
    public function disable()
    {
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

}
