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
 * Adds methods for publishable records
 *
 * @since 1.16
 */
trait StateServicePublishableTrait
{
    /**
     * @since 1.14
     * @return bool
     */
    public function isPublished(): bool
    {
        /** @var StateServiceInterface $this */
        return $this->is(StatableInterface::STATE_PUBLISHED);
    }

    /**
     * @since 1.14.3
     * @return bool
     */
    public function wasPublished(): bool
    {
        /** @var StateServiceInterface $this */
        return (bool)$this->getStateRecord()->was_published;
    }

    /**
     * @since 1.14
     * @return bool
     */
    public function publish(): bool
    {
        /** @var StateServiceInterface $this */
        return $this->update(StatableInterface::STATE_PUBLISHED);
    }
}
