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
 * SpaceSettingBehavior is a helper for Space models to acccess corresponding 
 * SpaceSetting models.
 * 
 * @since 0.6
 * @author luke
 */
class SpaceSettingBehavior extends CActiveRecordBehavior
{

    /**
     * Get an SpaceSetting Value
     * 
     * @param String $name of setting
     * @param String $moduleId of setting
     * @param String $default value when no setting exists
     * @return String
     */
    public function getSetting($name, $moduleId = "core", $default = "")
    {
        return SpaceSetting::Get($this->getOwner()->id, $name, $moduleId, $default);
    }

    /**
     * Sets an SpaceSetting
     * 
     * @param String $name
     * @param String $value
     * @param String $moduleId
     */
    public function setSetting($name, $value, $moduleId = "")
    {
        SpaceSetting::Set($this->getOwner()->id, $name, $value, $moduleId);
    }

}
