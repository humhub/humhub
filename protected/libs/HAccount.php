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
 * HAccount is a helper/singleton class for the account / authentication methods
 *
 * @package humhub.libs
 * @since 0.5
 * @author Luke
 */
class HAccount {

    /**
     * Constants for available modes
     */
    const AUTH_MODE_LOCAL = 'local';
    const AUTH_MODE_LDAP = 'ldap';

    /**
     * Singleton instance
     *
     * @var SiAccount
     */
    static private $instance = null;

    /**
     * @return HAccount instance
     */
    static public function getInstance() {
        if (null === self::$instance) {
            self::$instance = new self;
        }
        return self::$instance;
    }

    /**
     * Returns available authentication modes
     *
     * @return type
     */
    static function GetAuthModes() {

        if (!isset(Yii::app()->params['auth']['modes'])) {
            return array(self::AUTH_MODE_LOCAL);
        }

        return Yii::app()->params['auth']['modes'];
    }

    /**
     * Checks if given Auth Mode is available
     *
     * @param String $mode Authentication Mode
     */
    static function HasAuthMode($mode) {

        return in_array($mode, self::GetAuthModes());
    }

    /**
     * Checks if a given profile field can overwritten.
     *
     * e.g. LDAP Fields like FirstName cannot changed.
     *
     * @param String $fieldName Fieldname to check
     */
    static function IsProfileFieldWriteable($fieldName) {

        if (HAccount::HasAuthMode('ldap')) {
            $fieldMapping = Yii::app()->params['auth']['ldap']['fieldMapping'];
            if (Yii::app()->user->getAuthMode() == User::AUTH_MODE_LDAP) {


                if (isset($fieldMapping[$fieldName]))
                    return false;
            }
        }
        return true;
    }

}

?>
