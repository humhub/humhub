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
 * @author Lucas Bartholemy <lucas@bartholemy.com>
 * @package humhub.interfaces
 * @since 0.5
 */
interface IContentContainer
{

    public function getProfileImage();

    public function getUrl();

    /**
     * Returns the display name of the container
     * 
     * @since 0.11.0
     */
    public function getDisplayName();

    /**
     * Indiciates the given or current user can access private content
     * of this container.
     * 
     * @since 0.11.0
     * @param User $u
     * @return boolean
     */
    public function canAccessPrivateContent(User $u = null);
}
