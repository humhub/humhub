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
 * Adds methods for draftable records
 *
 * @since 1.16
 */
trait StateServiceDraftableTrait
{
    /**
     * @since 1.14
     * @return bool
     */
    public function isDraft(): bool
    {
        /** @var StateServiceInterface $this */
        return $this->is(StatableInterface::STATE_DRAFT);
    }

    /**
     * @since 1.14
     * @return bool
     */
    public function draft(): bool
    {
        /** @var StateServiceInterface $this */
        return $this->update(StatableInterface::STATE_DRAFT);
    }
}
