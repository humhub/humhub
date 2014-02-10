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
 * The SIContentBaseBahaviour is attached to all models which act as contentbase.
 *
 * A ContentBase can be Space or User. Essentially a ContentBase holds a
 * bunch of Content Objects.
 *
 * Each ContentBase should own a Wall Object.
 * A Content Base needs a GUID Database Field
 *
 * Additionally each ContentBase has a ProfileImage.
 *
 * Ideas/ToDo:
 *      - GetUrl  (Instead of Profile Url)
 *
 * @package humhub.behaviors
 * @since 0.5
 */
class HContentBaseBehavior extends HActiveRecordBehavior {
    // Auto Add Wall?
    // Auto Delete Wall?

    /**
     * Returns the Profile Image Object for this Content Base
     *
     * @return ProfileImage
     */
    public function getProfileImage() {

        if (get_class($this->getOwner()) == 'Space') {
            return new ProfileImage($this->getOwner()->guid, 'default_workspace');
        }
        return new ProfileImage($this->getOwner()->guid);
    }

}

?>
