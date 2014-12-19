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
 * This is caused by our view path for modules is also separated into modules/ folders.
 *
 * @author Lucas Bartholemy <lucas@bartholemy.com>
 * @package humhub.components
 * @since 0.5
 */
class HTheme extends CTheme
{

    public function getViewFile($controller, $viewName)
    {
        if (strpos($viewName, '.')) {
            return $this->getViewFileAliased($viewName);
        } else {
            return parent::getViewFile($controller, $viewName);
        }
    }

    /**
     * Searches for a themed version of an aliased view
     * 
     * @param string  $viewName
     * @return boolean
     */
    public function getViewFileAliased($viewName)
    {
        // Replace: application.views -> webroot.themes.CURRENTTHEME.views
        $viewName = str_replace('application.views.', 'webroot.themes.' . $this->getName() . '.views.', $viewName);

        // Replace: application.modules[_core].MODULEID.widgets.views -> webroot.themes.CURRENTTHEME.views.MODULEID.widgets
        $viewName = preg_replace('/application\.modules(?:_core)?\.(.*?)\.views\.(.*)/i', 'webroot.themes.' . $this->getName() . '.views.\1.\2', $viewName);

        // Replace: application.widget.views TO webroot.theme.THEMENAME.widget.views 
        $viewName = str_replace('application.widgets.views', 'webroot.themes.' . $this->getName() . '.views.widgets', $viewName);


        // Check if File exists
        $viewFile = Yii::getPathOfAlias($viewName) . '.php';
        if (is_file($viewFile)) {
            return Yii::app()->findLocalizedFile($viewFile);
        }

        return false;
    }

    /**
     * Returns an array of all installed themes.
     *
     * @return Array
     */
    public static function getThemes()
    {
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

    /**
     * Get file from theme directory, if it's exists, else from main directory
     *
     * @param      $file
     * @param bool $absolute
     *
     * @return string
     */
    public function getFileUrl($file, $absolute = false)
    {
        $file = ltrim($file, '/');
        $path = Yii::getPathOfAlias('webroot') . DIRECTORY_SEPARATOR . 'themes' . DIRECTORY_SEPARATOR . Yii::app()->theme->getName() . DIRECTORY_SEPARATOR . $file;

        if (file_exists($path)) {
            return $this->getBaseUrl($absolute) . '/' . $file;
        }

        return Yii::app()->getBaseUrl($absolute) . '/' . $file;
    }

    /**
     * Searches for a themed version of an language message file
     *
     * @param string $messageFile
     * @return string
     */
    public function getMessageFile($messageFile)
    {
    	// Replace: application.messages -> webroot.themes.CURRENTTHEME.messages
    	$themeMessageFile = str_replace(Yii::app()->basePath, $this->basePath, $messageFile);
    	
    	// Replace: application.modules[_core].MODULEID.messages -> webroot.themes.CURRENTTHEME.messages.MODULEID
    	$themeMessageFile = preg_replace('/modules(?:_core)?\/(.*?)\/messages\/(.*)\/(.*)/i', 'messages/\1/\2/\3', $themeMessageFile);
    	
    	// Check if file exists
    	if (is_file($themeMessageFile)) {
    		return $themeMessageFile;
    	}
    
    	return $messageFile;
    }
}

?>
