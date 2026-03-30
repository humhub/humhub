<?php

namespace humhub\modules\ldap\helpers;

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


}
