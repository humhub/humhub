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
 * HConsoleApplication is used as base console application.
 *
 * HConsoleApplication extends the default console application with some
 * functionalities about events and theming support.
 *
 * @author Lucas Bartholemy <lucas@bartholemy.com>
 * @package humhub.components
 * @since 0.8
 */
class HConsoleApplication extends CConsoleApplication {

    /**
     * Current theme name
     *
     * @var String
     */
    public $theme;

    /**
     * Initializes the console application and setup some event handlers
     */
    protected function init() {

        parent::init();

        $this->interceptor->start();
        $this->moduleManager->start();
        
        $this->interceptor->intercept($this);

        if ($this->hasEventHandler('onInit'))
            $this->onInit(new CEvent($this));
    }

    /**
     * Raised after the application inits.
     * @param CEvent $event the event parameter
     */
    public function onInit($event) {
        $this->raiseEvent('onInit', $event);
    }

    /**
     * Adds a new command to the Console Application
     *
     * @param String $name
     * @param String $file
     */
    public function addCommand($name, $file) {
        $this->getCommandRunner()->commands[$name] = $file;
    }

}

?>
