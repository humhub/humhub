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
 * HLdap provides a interface to the ADLDAP Class.
 *
 * @package humhub.libs
 * @since 0.5
 * @author Luke
 */
class HLdap {

    /**
     * @var adLDAP instance
     */
    public $ad = null;

    /**
     * @var SILDAP instance
     */
    static private $instance = null;

    /**
     * Returns the current HLdap Instance.
     *
     * @return HLdap
     */
    static public function getInstance() {

        require_once('adLDAP/adLDAP.php');
        if (null === self::$instance) {
            self::$instance = new self;
        }
        return self::$instance;
    }

    /**
     * Establish the adLdap connection
     */
    public function connect() {
        $this->ad = new adLDAP(Yii::app()->params['auth']['ldap']['adldapConfig']);
    }

    /**
     * Disconnects the adLdap connection
     */
    public function disconnect() {
        $this->ad->close();
        $this->ad = null;
    }

}

?>
