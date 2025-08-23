<?php

/*
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\libs;

use Stringable;
use Yii;

/**
 * UUID Generator Class provides static methods for creating or validating UUIDs.
 *
 * @package humhub.libs
 * @since 0.5
 */
class UUID
{
    public const UUID_LENGTH_MIN = 8 + 4 + 4 + 4 + 12;
    public const UUID_LENGTH_MAX = self::UUID_LENGTH_MIN + 6;

    /**
     * Creates a v4 UUID
     *
     * @return String
     * @noinspection PhpUnhandledExceptionInspection
     * @noinspection PhpDocMissingThrowsInspection
     */
    public static function v4(): string
    {
        return
            // 32 bits for "time_low"
            bin2hex(Yii::$app->security->generateRandomKey(4)) . '-'
            // 16 bits for "time_mid"
            . bin2hex(Yii::$app->security->generateRandomKey(2)) . '-'
            // 16 bits for "time_hi_and_version",
            // four most significant bits holds version number 4
            . dechex(mt_rand(0, 0x0fff) | 0x4000) . '-'
            // 16 bits, 8 bits for "clk_seq_hi_res",
            // 8 bits for "clk_seq_low",
            // two most significant bits holds zero and one for variant DCE1.1
            . dechex(mt_rand(0, 0x3fff) | 0x8000) . '-'
            // 48 bits for "node"
            . bin2hex(Yii::$app->security->generateRandomKey(6));
    }

    /**
     * Validates a given UUID and makes sure the returned value is a normalized UUID or null.
     *
     * Normalized means: with now curly brackets but with dashes: xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx, unless
     * configured with additional parameters. See parameter description.
     *
     * @param string|mixed $uuid
     * @param bool $withDash Dashes will be
     *        true  = ensured between blocks: xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx (default)
     *        false = removed: xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
     *        null  = untouched
     * @param bool|null $caseToLower Character chase will be
     *        true  = lowercase (default)
     *        false = uppercase
     *        null  = unchanged
     * @param bool|null $withCurlyBrackets Embracing curly brackets will be
     *        true  = ensured: {XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX}
     *        false = removed: XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX (default)
     *        null  = untouched
     *
     * @return string|null Valid, normalized GUID or null on invalid input
     * @since 1.15
     */
    public static function validate($uuid, ?bool $withDash = true, ?bool $caseToLower = true, ?bool $withCurlyBrackets = false): ?string
    {
        if ($uuid === null) {
            return null;
        }

        if (is_array($uuid)) {
            return null;
        }

        switch (true) {
            case is_string($uuid):
                // sp far so good!
                break;

            case is_scalar($uuid):
                // a non-string scalar cannot possibly have the correct format
            case !is_object($uuid):
                // other stuff (e.g., resources) can't be converted to string
                /**
                 * @noinspection PhpDuplicateSwitchCaseBodyInspection
                 * @noinspection RedundantSuppression
                 */
                return null;

            case $uuid instanceof Stringable || method_exists($uuid, '__toString'):
                $uuid = (string)$uuid;
                break;

            default:
                return null;
        }

        $uuid = trim($uuid);

        // is cheaper than running a regex
        $len = strlen($uuid);
        if ($len < self::UUID_LENGTH_MIN || $len > self::UUID_LENGTH_MAX) {
            return null;
        }

        if (
            !preg_match(
                '/^(\{)?([0-9a-f]{8})(-?)([0-9a-f]{4})\g3([0-9a-f]{4})\g3([0-9a-f]{4})\g3([0-9a-f]{12})(?(1)}|)$/i',
                $uuid,
                $match,
            )
        ) {
            return null;
        }

        if ($withDash === null && $caseToLower === null && $withCurlyBrackets === null) {
            return $match[0];
        }

        switch (true) {
            case $withDash === true:
                $dash = '-';
                break;

            case $withDash === false:
                $dash = '';
                break;

            default:
                // use as in string
                $dash = $match[3];
        }

        // remove delimiter from the matches
        unset($match[3]);

        $uuid = implode($dash, array_slice($match, 2, 5));

        switch (true) {
            case $caseToLower === true:
                $uuid = strtolower($uuid);
                break;

            case $caseToLower === false:
                $uuid = strtoupper($uuid);
                break;
        }

        if ($withCurlyBrackets ?? (bool)$match[1] ?? false) {
            $uuid = sprintf("{%s}", $uuid);
        }

        return $uuid;
    }

    /**
     * @deprecated since 1.15, use static::validate()
     * @see static::validate()
     * @codingStandardsIgnoreStart
     * @noinspection PhpMissingReturnTypeInspection
     * @noinspection PhpUnused
     */
    public static function is_valid($uuid)
    {
        return preg_match('/^\{?[0-9a-f]{8}-?[0-9a-f]{4}-?[0-9a-f]{4}-?' . '[0-9a-f]{4}-?[0-9a-f]{12}}?$/i', $uuid) === 1;
    }
}
