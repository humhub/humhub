<?php

/**
 * HumHub
 * Copyright © 2014 The HumHub Project
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 */

namespace humhub\components;

use Yii;
use humhub\models\ModuleEnabled;

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
    public $resourcesPath = 'resources';

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
     * Which means delete all (user-) data created by the module.
     *
     */
    public function disable()
    {

        // Seems not enabled
        if (!Yii::$app->hasModule($this->id)) {
            return false;
        }

        /*
          // Check this module is a SpaceModule
          if ($this->isSpaceModule()) {
          foreach ($this->getSpaceModuleSpaces() as $space) {
          $space->disableModule($this->getId());
          }
          }

          // Check this module is a UserModule
          if ($this->isUserModule()) {
          foreach ($this->getUserModuleUsers() as $user) {
          $user->disableModule($this->getId());
          }
          }
         */

        // Disable module in database
        $moduleEnabled = ModuleEnabled::findOne(['module_id' => $this->id]);
        if ($moduleEnabled != null) {
            $moduleEnabled->delete();
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

    protected function migrate()
    {
        // Auto Migrate (add module database changes)
        //Yii::import('application.commands.shell.ZMigrateCommand');
        //$migrate = ZMigrateCommand::AutoMigrate();
        return true;
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
     * Uninstalls a module
     *
     * You may overwrite this method to add more cleanup stuff.
     *
     * This method shall:
     *      - Delete all module files
     *      - Delete all modules tables, database changes
     */
    public function uninstall()
    {

        if ($this->isCoreModule) {
            throw new CException("Could not uninstall core modules!");
            return;
        }

        if (Yii::$app->hasModule($this->id)) {
            $this->disable();
        }

        // Use uninstall migration, when found
        $uninstallMigration = $this->getBasePath() . DIRECTORY_SEPARATOR . 'migrations' . DIRECTORY_SEPARATOR . 'uninstall.php';
        if (file_exists($uninstallMigration)) {
            Yii::import("application.commands.shell.*");
            ob_start();
            require_once($uninstallMigration);
            $migration = new uninstall;
            $migration->setDbConnection(Yii::$app->db);
            try {
                $migration->up();
            } catch (Exception $ex) {
                ;
            }
            ob_get_clean();
        }

        // Delete all executed migration by module
        $command = Yii::$app->db->createCommand('DELETE FROM migration WHERE module = :moduleId');
        $command->execute(array(':moduleId' => $this->getId()));

        Yii::$app->moduleManager->removeModuleFolder($this->getId());

        ModuleManager::flushCache();
    }

    /**
     * Installs a module
     */
    public function install()
    {
        $this->migrate();
        return true;
    }

    /**
     * This method is called after an update is performed.
     * You may extend it with your own update process.
     *
     */
    public function update()
    {
        $this->migrate();
        return true;
    }

    /**
     * Removes module folder in case of uninstall or update
     */
    public function removeModuleFolder()
    {

        $moduleBackupFolder = Yii::$app->getRuntimePath() . DIRECTORY_SEPARATOR . 'module_backups';
        if (!is_dir($moduleBackupFolder)) {
            if (!@mkdir($moduleBackupFolder)) {
                throw new CException("Could not create module backup folder!");
            }
        }

        $backupFolderName = $moduleBackupFolder . DIRECTORY_SEPARATOR . $this->getId() . "_" . time();
        if (!@rename($this->getBasePath(), $backupFolderName)) {
            throw new CException("Could not remove module folder!");
        }
    }

    /**
     * Indicates that module acts as Space Module.
     *
     * @return boolean
     */
    public function isSpaceModule()
    {
        foreach ($this->getBehaviors() as $name => $behavior) {
            if ($behavior instanceof \humhub\core\space\behaviors\SpaceModule) {
                return true;
            }
        }

        return false;
    }

    /**
     * Indicates that module acts as User Module.
     *
     * @return boolean
     */
    public function isUserModule()
    {
        foreach ($this->getBehaviors() as $name => $behavior) {
            if ($behavior instanceof \humhub\core\user\behaviors\UserModule) {
                return true;
            }
        }

        return false;
    }

    public function canDisable()
    {
        return true;
    }

    public function canUninstall()
    {
        return true;
    }

}
