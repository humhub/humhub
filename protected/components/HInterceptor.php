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
 * HInterceptor allows modules to attach events and behaviors to all SIComponents.
 *
 * @author Lucas Bartholemy <lucas@bartholemy.com>
 * @package humhub.components
 * @since 0.5
 */
class HInterceptor extends CApplicationComponent {

    /**
     * @var Array list of events and callbacks
     */
    private $events = array();

    /**
     * Starts interceptor service
     */
    public function start() {

    }

    /**
     * Intercepts a given object instance
     *
     * @param Object $obj
     */
    public function intercept($obj) {

        // Attach Event Handler for this class
        $className = get_class($obj);
        if (isset($this->events[$className])) {
            foreach ($this->events[$className] as $event) {
                $obj->attachEventHandler($event[0], $event[1]);
            }
        }

        // Install also for parent classes
        $parentClassName = get_parent_class($className);
        while ($parentClassName !== false) {
            if (isset($this->events[$parentClassName])) {
                foreach ($this->events[$parentClassName] as $event) {
                    $obj->attachEventHandler($event[0], $event[1]);
                }
            }
            $parentClassName = get_parent_class($parentClassName);
        }
    }

    /**
     * Attaches an event handler
     *
     * @param String $className
     * @param String $eventName
     * @param type $handler
     */
    public function attachEventHandler($className, $eventName, $handler=null) {

        # if ($className == 'ModuleManager') {#
        #    // There is already an instance
        #    Yii::app()->moduleManager->attachEventHandler($eventName, $handler);
        #    return;
        #}

        if (!isset($this->events[$className]))
            $this->events[$className] = array();

        $this->events[$className][] = array($eventName, $handler);
    }

}

?>
