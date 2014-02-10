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
 * Interface for Searchable Models
 *
 * @package humhub.interfaces
 * @since 0.5
 * @author Luke
 */
interface ISearchable {

    /**
     * Returns an array of informations which are required to index the object.
     *
     * Example Array:
     * return array(
     *       // Assignments
     *      'belongsToType' => 'Space',		// or user *MUST BE SET*	(Space, User)
     *      'belongsToId' => 2,			// Id of User or Space	(i
     *      'belongsToGuid' => 'asdf',		// Space or Users GUID
     *
     *      // Informations about the record
     *      'model' => 'User',			// Model of Result
     *      'pk' => '1',				// Primary Key for ->findByPk
     *      'title' => 'asdf',			// Title of Result
     *      'url' => '/zeros_social_intranet/...',	// Target URL
     *
     *      // Some extra indexed fields
     *      'fieldX' => 'valueY',
     * );
     *
     * @return Array of attributes required for indexing
     */
    public function getSearchAttributes();

    /**
     * Returns the output of the search result.
     *
     * @return String HTML output of the search result
     */
    public function getSearchResult();
}

?>
