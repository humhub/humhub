<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2023 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\libs;

use humhub\interfaces\StatableInterface;

/**
 * Adds methods for publishable records
 *
 * @since 1.15
 */
trait StateServicePublishableTrait
{
    public function isPublished(): bool
    {
        return $this->is(StatableInterface::STATE_PUBLISHED);
    }

    public function publish(): bool
    {
        return $this->update(StatableInterface::STATE_PUBLISHED);
    }
}
