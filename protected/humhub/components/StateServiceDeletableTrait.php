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
trait StateServiceDeletableTrait
{
    /**
     * @since 1.14
     * @return bool
     */
    public function isDeleted(): bool
    {
        /** @var StateServiceInterface $this */
        return $this->is(StatableInterface::STATE_DELETED);
    }

    /**
     * @since 1.14
     * @return bool
     */
    public function delete(): bool
    {
        /** @var StateServiceInterface $this */
        return $this->update(StatableInterface::STATE_DELETED);
    }
}
