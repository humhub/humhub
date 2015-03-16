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
 * This class contains a lot of html helpers for the views
 *
 * @package humhub.libs
 * @since 0.5
 */
class Helpers
{

    /**
     * Shorten a text string
     * @param string $text - Text string you will shorten
     * @param integer $length - Count of characters to show
     *
     * */
    public static function truncateText($text, $length)
    {

        $length = abs((int) $length);
        if (strlen($text) > $length) {
            $text = preg_replace("/^(.{1,$length})(\s.*|$)/s", '\\1...', $text);
        }
        $text = str_replace("<br />", "", $text);

        return($text);
    }

    public static function trimText($text, $length)
    {

        $length = abs((int) $length);
        $textlength = mb_strlen($text);
        if ($textlength > $length) {
            $text = self::substru($text, 0, $textlength - ($textlength - $length));
            $text = $text . "...";
        }
        $text = str_replace("<br />", "", $text);

        return($text);
    }

    /*     *
     * Compare two arrays values
     * @param array $a - First array to compare against..
     * @param array $b - Second array
     *
     * convert Objects: Helpers::arrayCompVal((array)$obj1, (array)$obj2)
     * 
     * */

    public static function arrayCompVal($a, $b)
    {
        if (!is_array($a) || !is_array($b))
            return false;
        sort($a);
        sort($b);
        return $a == $b;
    }

    /**
     * Temp Function to use UTF8 SubStr
     *
     * @param type $str
     * @param type $from
     * @param type $len
     * @return type
     */
    public static function substru($str, $from, $len)
    {
        return preg_replace('#^(?:[\x00-\x7F]|[\xC0-\xFF][\x80-\xBF]+){0,' . $from . '}' . '((?:[\x00-\x7F]|[\xC0-\xFF][\x80-\xBF]+){0,' . $len . '}).*#s', '$1', $str);
    }

    /**
     * Get a readable time format from seconds
     * @param string $sekunden - Seconds you will formatting
     * */
    public static function getFormattedTime($sekunden)
    {

        $negative = false;
        $minus = "";
        if ($sekunden < 0) {
            $negative = true;
            $sekunden = $sekunden * (-1);
            $minus = "-";
        }

        $minuten = bcdiv($sekunden, '60', 0);
        $sekunden = bcmod($sekunden, '60');

        $stunden = bcdiv($minuten, '60', 0);
        $minuten = bcmod($minuten, '60');

        if ($minuten < 10) {
            $minuten = "0" . $minuten;
        }

        $tage = bcdiv($stunden, '24', 0);
        $stunden = bcmod($stunden, '24');



        return $minus . $stunden . ':' . $minuten;
    }

    /**
     * Returns bytes of a PHP Ini Setting Value
     * E.g. 10M will converted into 10485760
     * 
     * Source: http://php.net/manual/en/function.ini-get.php
     * 
     * @param String $val 
     * @return int bytes
     */
    public static function GetBytesOfPHPIniValue($val)
    {
        $val = trim($val);
        $last = strtolower($val[strlen($val) - 1]);
        switch ($last) {
            case 'g':
                $val *= 1024;
            case 'm':
                $val *= 1024;
            case 'k':
                $val *= 1024;
        }

        return $val;
    }

    /**
     * Returns a unique string
     * 
     * @return string unique
     */
    public static function GetUniqeId()
    {
        return str_replace('.', '', uniqid('', true));
    }

    /**
     * Checks if the class has this class as one of its parents 
     * 
     * @param string $className
     * @param string $type
     * @return boolean
     */
    public static function CheckClassType($className, $type = "")
    {
        $className = preg_replace("/[^a-z0-9]/i", "", $className);

        if (is_array($type)) {
            foreach ($type as $t) {
                if (class_exists($className) && is_subclass_of($className, $t)) {
                    return true;
                }
            }
        } else {
            if (class_exists($className) && is_subclass_of($className, $type)) {
                return true;
            }
        }

        throw new CException("Invalid class type! |" . $className . "|");
    }

}
