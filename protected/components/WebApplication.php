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
 * WebApplication is the base WebApplication class
 *
 * @author Lucas Bartholemy <lucas@bartholemy.com>
 * @package humhub.components
 * @since 0.5
 */
class WebApplication extends CWebApplication
{

    /**
     * Inits the webapplication
     *
     * Also start interceptor to add event support
     */
    protected function init()
    {

        parent::init();

        $this->interceptor->start();
        $this->moduleManager->start();

        $this->interceptor->intercept($this);

        if ($this->hasEventHandler('onInit'))
            $this->onInit(new CEvent($this));
    }

    /**
     * This event is raised after the init is executed.
     * @param CEvent $event the event parameter
     */
    public function onInit($event)
    {
        $this->raiseEvent('onInit', $event);
    }

    /**
     * Returns an array of available language codes
     *
     * @return Array
     */
    public static function getLanguages()
    {

        $languages = array();
        $languages['en'] = 'en';

        $files = scandir(Yii::app()->basePath . '/messages');
        foreach ($files as $file) {
            if ($file == '.' || $file == '..')
                continue;
            $languages[$file] = $file;
        }
        return $languages;
    }

}

?>
