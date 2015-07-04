<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\content\components;

use \humhub\components\ActiveRecord;

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
class ContentContainerActiveRecord extends ActiveRecord
{

    /**
     * Returns the Profile Image Object for this Content Base
     *
     * @return ProfileImage
     */
    public function getProfileImage()
    {

        if (get_class($this) == 'Space') {
            return new \humhub\libs\ProfileImage($this->guid, 'default_space');
        }
        return new \humhub\libs\ProfileImage($this->guid);
    }

    /**
     * Returns the Profile Banner Image Object for this Content Base
     *
     * @return ProfileBannerImage
     */
    public function getProfileBannerImage()
    {

        return new \humhub\libs\ProfileBannerImage($this->guid);
    }

    /**
     * Should be overwritten by implementation
     */
    public function getUrl()
    {
        return $this->createUrl();
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

    public function getWallOut()
    {
        return "Default Wall Output for Class " . get_class($this);
    }

}

?>
