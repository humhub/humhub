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
 * HTheme is an overwrite of CTheme
 *
 * This is caused by our view path for modules is also separeted into modules/ folders.
 *
 * @author Lucas Bartholemy <lucas@bartholemy.com>
 * @package humhub.components
 * @since 0.5
 */
class HTheme extends CTheme {
    /**
     * Finds the view file for the specified controller's view.
     *
     * @param CController $controller the controller
     * @param string $viewName the view name
     * @return string the view file path. False if the file does not exist.
     */
    /*
      public function getViewFile($controller, $viewName) {
      $moduleViewPath = $this->getViewPath();
      if (($module = $controller->getModule()) !== null) {
      $moduleViewPath.='/modules/' . $module->getId();
      #                 ^^^^^^      added modules here
      }
      return $controller->resolveViewFile($viewName, $this->getViewPath() . '/' . $controller->getUniqueId(), $this->getViewPath(), $moduleViewPath);
      }
     */

    /**
     * Returns an array of all installed themes.
     *
     * @return Array
     */
    public static function getThemes() {
        $themes = array();
        $themePath = Yii::app()->themeManager->getBasePath();

        foreach (scandir($themePath) as $file) {
            if ($file == "." || $file == ".." || !is_dir($themePath . DIRECTORY_SEPARATOR . $file)) {
                continue;
            }
            $themes[$file] = $file;
        }
        return $themes;
    }

}

?>
