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
 * This behavior should attached to a HWebModule when it provides a user profile module.
 * 
 * @package humhub.modules_core.user.behaviors
 * @author luke
 */
class UserModuleBehavior extends CBehavior
{

    /**
     * Returns current space by context
     * 
     * @return Space
     */
    public function getUser()
    {
        return Yii::app()->getController()->getUser();
    }

    /**
     * Checks if this module is enabled on given user profile.
     * 
     * @param User $user
     * @return boolean
     */
    public function isUserModuleEnabled(User $user = null)
    {
        if ($user == null) {
            $user = $this->getUser();
        }

        return $user->isModuleEnabled($this->getOwner()->getId());
    }

    /**
     * Returns module name for users of your module.
     * You may want to overwrite it in your module.
     * 
     * @return String
     */
    public function getUserModuleName()
    {
        return $this->getOwner()->getName();
    }

    /**
     * Returns module description for users of your module.
     * You may want to overwrite it in your module.
     * 
     * @return String
     */
    public function getUserModuleDescription()
    {
        return $this->getOwner()->getDescription();
    }

    /**
     * Returns module config url for users of your module.
     * You may want to overwrite it in your module.
     * 
     * @return String
     */
    public function getUserModuleConfigUrl(User $user)
    {
        return "";
    }

    /**
     * Returns the module image for users.
     * You may want to overwrite with an special user profile image.
     * 
     * @return String
     */
    public function getUserModuleImage()
    {
        return $this->getOwner()->getImage();
    }

    /**
     * Enables this module on given user
     * 
     * @param User $user
     */
    public function enableUserModule(User $user)
    {
        
    }

    /**
     * Disables this module on given user
     * 
     * You may want to overwrite this function and delete e.g. created
     * content objects.
     * 
     * @param User $user
     */
    public function disableUserModule(User $user)
    {
        
    }

    /**
     * Returns a list of all users where this Module is enabled.
     * 
     * @return Array User
     */
    public function getUserModuleUsers()
    {
        $users = array();

        foreach (User::model()->findAll() as $u) {
            if ($u->isModuleEnabled($this->owner->getId())) {
                $users[] = $u;
            }
        }

        return $users;
    }

}
