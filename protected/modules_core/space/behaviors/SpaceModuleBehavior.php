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
 * This behavior should attached to a HWebModule when it provides a space module.
 *
 * @author luke
 * @package humhub.modules_core.space.behaviors
 */
class SpaceModuleBehavior extends CBehavior
{

    /**
     * Returns current space by context
     * 
     * @return Space
     */
    public function getSpace()
    {
        return Yii::app()->getController()->getSpace();
    }

    /**
     * Checks if this module is enabled on given space.
     * 
     * @param Space $space
     * @return boolean
     */
    public function isSpaceModuleEnabled(Space $space = null)
    {
        if ($space == null) {
            $space = $this->getSpace();
        }

        return $space->isModuleEnabled($this->getOwner()->getId());
    }

    /**
     * Returns module name for spaces of your module.
     * You may want to overwrite it in your module.
     * 
     * @return String
     */
    public function getSpaceModuleName()
    {
        return $this->getOwner()->getName();
    }

    /**
     * Returns module description for spaces of your module.
     * You may want to overwrite it in your module.
     * 
     * @return String
     */
    public function getSpaceModuleDescription()
    {
        return $this->getOwner()->getDescription();
    }

    /**
     * Returns module config url for spaces of your module.
     * You may want to overwrite it in your module.
     * 
     * @return String
     */
    public function getSpaceModuleConfigUrl(Space $space)
    {
        return "";
    }

    /**
     * Returns the module image for space admins.
     * You may want to overwrite with an special space image.
     * 
     * @return String
     */
    public function getSpaceModuleImage()
    {
        return $this->getOwner()->getImage();
    }

    /**
     * Enables this module on given space
     * 
     * @param Space $space
     */
    public function enableSpaceModule(Space $space)
    {
        
    }

    /**
     * Disables this module on given space
     * 
     * You may want to overwrite this function and delete e.g. created
     * content objects.
     * 
     * @param Space $space
     */
    public function disableSpaceModule(Space $space)
    {
        
    }

    /**
     * Returns a list of all spaces where this SpaceModule is
     * enabled.
     * 
     * @return Array Space
     */
    public function getSpaceModuleSpaces()
    {
        $spaces = array();

        foreach (Space::model()->findAll() as $s) {
            if ($s->isModuleEnabled($this->owner->getId())) {
                $spaces[] = $s;
            }
        }

        return $spaces;
    }

}
