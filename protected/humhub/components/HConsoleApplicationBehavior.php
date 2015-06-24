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
 * HConsoleApplicationBehavior is added to all console applications.
 *
 * @author Lucas Bartholemy <lucas@bartholemy.com>
 * @package humhub.components
 * @since 0.5
 */
class HConsoleApplicationBehavior extends CBehavior {

    /**
     * Adds a null view renderer.
     *
     * @return null
     */
    public function getViewRenderer() {
        return NULL;
    }

    /**
     * Returns null theme.
     *
     * @return null
     */
    public function getTheme() {
        return NULL;
    }

}

?>
