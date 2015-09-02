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

/**
 * Base Class for Modules / Extensions
 *
 * @author luke
 */
class HWebModule extends CWebModule
{

    /**
     * Loaded Module JSON File
     *
     * @var Array
     */
    private $_moduleInfo = null;

    /**
     * Indicates that module is required by core
     *
     * @var Boolean
     */
    public $isCoreModule = false;

    /**
     * URL to assets
     *
     * @var String
     */
    private $_assetsUrl;

    /**
     * Preinits the module, attaches behaviors.
     * e.g. UserModuleBehavior or SpaceModuleBehavior
     */
    public function preinit()
    {
        $this->attachBehaviors($this->behaviors());

        parent::preinit();
    }

    /**
     * Add behaviors to this module
     *
     * You may want to enable one of these behavior to alos make this module
     * available on space and/or user context.
     *
     * See related behaviors classes for more details.
     *
     * @return Array
     */
    public function behaviors()
    {
        return array(
                /*
                  'SpaceModuleBehavior' => array(
                  'class' => 'application.modules_core.space.behaviors.SpaceModuleBehavior',
                  ),

                  'UserModuleBehavior' => array(
                  'class' => 'application.modules_core.user.behaviors.UserModuleBehavior',
                  ),
                 */
        );
    }

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
        if (is_file($this->getAssetsPath() . DIRECTORY_SEPARATOR . 'module_image.png')) {
            return $this->getAssetsUrl() . '/module_image.png';
        }
        if (Yii::app()->theme && Yii::app()->theme != "") {
            // get default image from theme (if exists)
            $image = Yii::app()->theme->getFileUrl('/img/default_module.jpg');
        } else {
            $image = Yii::app()->getBaseUrl() . '/img/default_module.jpg';
        }

        return $image;
    }

    /**
     * Returns URL of configuration controller for this module.
     *
     * You may overwrite this method to provide advanced module configuration
     * possibilities.
     *
     * @return string
     */
    public function getConfigUrl()
    {
        return "";
    }

    /**
     * Checks whether this module is enabled or not
     *
     * @return boolean
     */
    public function isEnabled()
    {
        return Yii::app()->moduleManager->isEnabled($this->getId());
    }

    /**
     * Enables this module
     */
    public function enable()
    {
        if (!$this->isEnabled()) {

            $moduleEnabled = ModuleEnabled::model()->findByPk($this->getId());
            if ($moduleEnabled == null) {

                $moduleEnabled = new ModuleEnabled();
                $moduleEnabled->module_id = $this->getId();
                $moduleEnabled->save();

                // Auto Migrate (add module database changes)
                Yii::import('application.commands.shell.ZMigrateCommand');
                $migrate = ZMigrateCommand::AutoMigrate();
            }
        }
    }

    /**
     * Disables a module
     *
     * Which means delete all (user-) data created by the module.
     *
     */
    public function disable()
    {
        if (!$this->isEnabled() || $this->isCoreModule)
            return false;

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

        // Disable module in database
        $moduleEnabled = ModuleEnabled::model()->findByPk($this->getId());
        if ($moduleEnabled != null) {
            $moduleEnabled->delete();
        }

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

        $moduleJson = file_get_contents($this->getPath() . DIRECTORY_SEPARATOR . 'module.json');
        return CJSON::decode($moduleJson);
    }

    /**
     * Returns Base Path of Module
     */
    public function getPath()
    {
        $reflection = new ReflectionClass($this);
        return dirname($reflection->getFileName());
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

        if ($this->isEnabled()) {
            $this->disable();
        }

        // Use uninstall migration, when found
        $uninstallMigration = $this->getPath() . DIRECTORY_SEPARATOR . 'migrations' . DIRECTORY_SEPARATOR . 'uninstall.php';
        if (file_exists($uninstallMigration)) {
            Yii::import("application.commands.shell.*");
            ob_start();
            require_once($uninstallMigration);
            $migration = new uninstall;
            $migration->setDbConnection(Yii::app()->db);
            try {
                $migration->up();
            } catch (Exception $ex) {
                ;
            }
            ob_get_clean();
        }

        // Delete all executed migration by module
        $command = Yii::app()->db->createCommand('DELETE FROM migration WHERE module = :moduleId');
        $command->execute(array(':moduleId' => $this->getId()));

        Yii::app()->moduleManager->removeModuleFolder($this->getId());

        ModuleManager::flushCache();
    }

    /**
     * Installs a module
     */
    public function install()
    {
        // Execute all available migrations
        Yii::import('application.commands.shell.ZMigrateCommand');
        $migrate = ZMigrateCommand::AutoMigrate();
    }

    /**
     * This method is called after an update is performed.
     * You may extend it with your own update process.
     *
     */
    public function update()
    {
        // Auto Migrate (add module database changes)
        Yii::import('application.commands.shell.ZMigrateCommand');
        $migrate = ZMigrateCommand::AutoMigrate();
    }

    /**
     * Removes module folder in case of uninstall or update
     */
    public function removeModuleFolder()
    {

        $moduleBackupFolder = Yii::app()->getRuntimePath() . DIRECTORY_SEPARATOR . 'module_backups';
        if (!is_dir($moduleBackupFolder)) {
            if (!@mkdir($moduleBackupFolder)) {
                throw new CException("Could not create module backup folder!");
            }
        }

        $backupFolderName = $moduleBackupFolder . DIRECTORY_SEPARATOR . $this->getId() . "_" . time();
        if (!@rename($this->getPath(), $backupFolderName)) {
            throw new CException("Could not remove module folder!");
        }
    }

    /**
     * Get assets url
     *
     * @return String url to assets
     */
    public function getAssetsUrl()
    {
        if ($this->_assetsUrl === null) {
            if ($this->getPath() != "") {
                $this->_assetsUrl = Yii::app()->getAssetManager()->publish($this->getAssetsPath(), false, -1, YII_DEBUG);
            }
        }
        return $this->_assetsUrl;
    }

    /**
     * Get assets path
     *
     * @return String path to assets
     */
    public function getAssetsPath()
    {
        $path = $this->getPath() . DIRECTORY_SEPARATOR . 'assets';

        if (is_dir($path)) {
            return $path;
        }

        return "";
    }

    /**
     * Indicates that module acts as Space Module.
     *
     * @return boolean
     */
    public function isSpaceModule()
    {
        if (array_key_exists('SpaceModuleBehavior', $this->behaviors())) {
            return true;
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
        if (array_key_exists('UserModuleBehavior', $this->behaviors())) {
            return true;
        }
        return false;
    }

}
