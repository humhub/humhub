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
 * Simple class that holds some information at runtime
 *
 * @package humhub.libs
 * @since 0.5
 */
class RuntimeCache {

    /**
     *
     * @var Array holds all "cached" Informations
     */
    static $data = array();

    /**
     * Removes an item
     *
     * @param type $key
     */
    static public function Remove($key) {
        if (isset(self::$data[$key]))
            unset(self::$data[$key]);
    }

    /**
     * Returns an item
     *
     * @param type $key
     * @return mixed is the cached object
     */
    static public function get($key) {
        if (isset(self::$data[$key]))
            return self::$data[$key];

        return false;
    }

    /**
     * Sets an new item
     *
     * @param type $key
     * @param mixed $value
     */
    static public function set($key, $val) {
        self::$data[$key] = $val;
    }

}

?>
