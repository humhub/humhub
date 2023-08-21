<?php

/*
 * @link      https://www.humhub.org/
 * @copyright Copyright (c) 2023 HumHub GmbH & Co. KG
 * @license   https://www.humhub.com/licences
 */

namespace humhub\libs;

use humhub\interfaces\StatableInterface;

/**
 * Adds methods for deletable records
 *
 * @since 1.16
 */
trait StateServiceSoftDeletableTrait
{
    public function isSoftDeleted(): bool
    {
        return $this->is(StatableInterface::STATE_SOFT_DELETED);
    }

    public function softDelete(): bool
    {
        return $this->update(StatableInterface::STATE_SOFT_DELETED);
    }
}
