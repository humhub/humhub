<?php

/*
 * @link      https://www.humhub.org/
 * @copyright Copyright (c) 2023 HumHub GmbH & Co. KG
 * @license   https://www.humhub.com/licences
 */

namespace humhub\components;

use humhub\interfaces\StatableInterface;
use humhub\interfaces\StateServiceInterface;

/**
 * Adds methods for deletable records
 *
 * @since 1.16
 */
trait StateServiceSoftDeletableTrait
{
    public function isSoftDeleted(): bool
    {
        /** @var StateServiceInterface $this */
        return $this->is(StatableInterface::STATE_SOFT_DELETED);
    }

    public function softDelete(): bool
    {
        /** @var StateServiceInterface $this */
        return $this->update(StatableInterface::STATE_SOFT_DELETED);
    }
}
