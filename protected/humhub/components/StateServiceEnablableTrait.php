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
 * Adds methods for enablable records
 *
 * @since 1.16
 */
trait StateServiceEnablableTrait
{
    public function isEnabled(): bool
    {
        /** @var StateServiceInterface $this */
        return $this->is(StatableInterface::STATE_ENABLED);
    }

    public function enable(): bool
    {
        /** @var StateServiceInterface $this */
        return $this->update(StatableInterface::STATE_ENABLED);
    }

    public function isDisabled(): bool
    {
        /** @var StateServiceInterface $this */
        return $this->is(StatableInterface::STATE_DISABLED);
    }

    public function disable(): bool
    {
        /** @var StateServiceInterface $this */
        return $this->update(StatableInterface::STATE_DISABLED);
    }
}
