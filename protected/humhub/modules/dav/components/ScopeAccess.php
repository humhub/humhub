<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\dav\components;

use humhub\modules\oauth\components\ScopeRegistry;

/**
 * Whether the token of a request holds a scope: one of those required, or `full`.
 *
 * @since 1.20
 */
final class ScopeAccess
{
    /**
     * @param string[] $scopes the effective scopes of the grant
     */
    public function __construct(public readonly array $scopes)
    {
    }

    /**
     * @param string|string[] $required any of them suffices
     */
    public function allows(string|array $required): bool
    {
        return in_array(ScopeRegistry::FULL, $this->scopes, true)
            || array_intersect((array)$required, $this->scopes) !== [];
    }
}
