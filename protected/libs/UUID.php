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
 * UUID Generator Class provides static methods for creating or validating UUIDs.
 *
 * @package humhub.libs
 * @since 0.5
 * @author Luke
 */
class UUID
{

    /**
     * Creates an v4 UUID
     *
     * @return String
     */
    public static function v4()
    {
        return
                // 32 bits for "time_low"
                bin2hex(Yii::app()->getSecurityManager()->generateRandomBytes(4, true)) . "-" .
                // 16 bits for "time_mid"
                bin2hex(Yii::app()->getSecurityManager()->generateRandomBytes(2, true)) . "-" .
                // 16 bits for "time_hi_and_version",
                // four most significant bits holds version number 4
                dechex(mt_rand(0, 0x0fff) | 0x4000) . "-" .
                // 16 bits, 8 bits for "clk_seq_hi_res",
                // 8 bits for "clk_seq_low",
                // two most significant bits holds zero and one for variant DCE1.1
                dechex(mt_rand(0, 0x3fff) | 0x8000) . "-" .
                // 48 bits for "node"
                bin2hex(Yii::app()->getSecurityManager()->generateRandomBytes(6, true));
    }

    /**
     * Validates a given UUID
     *
     * @param String $uuid
     * @return boolean
     */
    public static function is_valid($uuid)
    {
        return preg_match('/^\{?[0-9a-f]{8}\-?[0-9a-f]{4}\-?[0-9a-f]{4}\-?' .
                        '[0-9a-f]{4}\-?[0-9a-f]{12}\}?$/i', $uuid) === 1;
    }

}

?>