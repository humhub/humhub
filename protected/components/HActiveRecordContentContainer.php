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
 * 
 * Required Attributes:
 *      - wall_id
 *      - guid
 *  
 * Required Methods:
 *      - getProfileImage()
 *      - getUrl()
 * 
 */
class HActiveRecordContentContainer extends HActiveRecord implements IContentContainer
{

    /**
     * Returns the Profile Image Object for this Content Base
     *
     * @return ProfileImage
     */
    public function getProfileImage()
    {

        if (get_class($this) == 'Space') {
            return new ProfileImage($this->guid, 'default_space');
        }
        return new ProfileImage($this->guid);
    }

    /**
     * Returns the Profile Banner Image Object for this Content Base
     *
     * @return ProfileBannerImage
     */
    public function getProfileBannerImage()
    {

        return new ProfileBannerImage($this->guid);
    }

    /**
     * Should be overwritten by implementation
     */
    public function getUrl()
    {
        return "";
    }

    /**
     * Check write permissions on content container.
     * Overwrite this with your own implementation.
     * 
     * @param type $userId
     * @return boolean
     */
    public function canWrite($userId = "")
    {
        return false;
    }

    /**
     * Creates url in content container scope.
     * E.g. add uguid or sguid parameter to parameters.
     * 
     * @param type $route
     * @param type $params
     * @param type $ampersand
     */
    public function createUrl($route, $params = array(), $ampersand = '&')
    {
        return "";
    }

    /**
     * Returns the display name of content container
     * 
     * @since 0.11.0
     * @return string
     */
    public function getDisplayName()
    {
        return "Container: " . get_class($this) . " - " . $this->getPrimaryKey();
    }

    public function canAccessPrivateContent(User $user = null)
    {
        return false;
    }

}

?>
