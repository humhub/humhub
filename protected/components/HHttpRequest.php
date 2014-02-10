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
 * HHttpRequest extends the CHttpRequest.
 *
 * @author Lucas Bartholemy <lucas@bartholemy.com>
 * @package humhub.components
 * @since 0.5
 */
class HHttpRequest extends CHttpRequest {

    public $csrfTokenName = 'CSRF_TOKEN';

    /**
     * Returns whether this is an AJAX (XMLHttpRequest) request.
     * @return boolean whether this is an AJAX (XMLHttpRequest) request.
     */
    public function getIsAjaxRequest() {

        if (!parent::getIsAjaxRequest()) {
            if (isset($_REQUEST['ajax'])) {
                return true;
            }
            return false;
        }
        return true;
    }

}

?>
