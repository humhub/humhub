<?php

namespace humhub\modules\ldap\helpers;

use humhub\libs\StringHelper;
use humhub\modules\ldap\authclient\LdapAuth;
use Yii;

/**
 * This class contains LDAP helpers
 *
 * @since 0.5
 */
class LdapHelper
{
    /**
     * Checks if LDAP is supported by this environment.
     *
     * @return bool
     */
    public static function isLdapAvailable()
    {
        if (!function_exists('ldap_bind')) {
            return false;
        }

        return true;
    }

    /**
     * Checks if at least one LDAP Authclient is enabled.
     *
     * @return bool
     */
    public static function isLdapEnabled()
    {
        foreach (Yii::$app->authClientCollection->getClients() as $authClient) {
            if ($authClient instanceof LdapAuth) {
                return true;
            }
        }

        return false;
    }

    public static function isBinary(string $value): bool
    {
        if (!mb_check_encoding($value, 'UTF-8') || str_contains($value, "\0")) {
            return true;
        }
        return false;
    }

    public static function dropMultiValues(array $attributes, array $keepMultiValueKeys = []): array
    {
        $normalized = [];

        foreach ($attributes as $name => $value) {
            if (is_array($value) && !in_array($name, $keepMultiValueKeys)) {
                if (isset($value[0])) {
                    $normalized[$name] = $value[0];
                }
            } else {
                $normalized[$name] = $value;
            }
        }

        return $normalized;
    }


    /**
     * Sanitizes a raw LDAP result array by removing numerical indices and 'count' keys, and unwrapping
     * single-value arrays.
     *
     * @param array $rawEntry
     * @return array
     */
    public static function cleanLdapResponse(array $rawEntry): array
    {
        $cleanAttributes = [];

        foreach ($rawEntry as $key => $values) {
            if (is_int($key)) {
                continue;
            }

            $key = strtolower((string)$key);

            if (is_array($values)) {
                // Unset first value and reset array (php ldap always adds count value on each entry)
                unset($values['count']);
                $values = array_values($values);

                if (count($values) === 1) {
                    if ($key === 'objectguid') {
                        $value = StringHelper::binaryToGuid($values[0]);
                    } else {
                        $value = $values[0];
                    }
                } else {
                    $value = $values;
                }

                $cleanAttributes[$key] = $value;
            } else {
                $cleanAttributes[$key] = $values;
            }
        }

        // Ensure memberof Array and strtolower
        if (isset($cleanAttributes['memberof'])) {
            $cleanAttributes['memberof'] = is_array($cleanAttributes['memberof'])
                ? array_map('strtolower', $cleanAttributes['memberof'])
                : [strtolower($cleanAttributes['memberof'])];
        }

        return $cleanAttributes;
    }


}
