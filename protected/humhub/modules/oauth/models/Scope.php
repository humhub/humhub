<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\oauth\models;

/**
 * A scope a grant can hold. A module scope id starts with the module id and a colon.
 *
 * @since 1.20
 */
final class Scope
{
    public function __construct(
        public readonly string $id,
        public readonly string $label,
    ) {
    }
}
