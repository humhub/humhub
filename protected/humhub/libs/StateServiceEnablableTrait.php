<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2023 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\libs;

use humhub\interfaces\StatableInterface;

/**
 * Adds methods for deletable records
 *
 * @since 1.15
 */
trait StateServiceEnablableTrait
{
    public function isEnabled(): bool
    {
        return $this->is(StatableInterface::STATE_ENABLED);
    }

    public function enable(): bool
    {
        return $this->update(StatableInterface::STATE_ENABLED);
    }

    public function isDisabled(): bool
    {
        return $this->is(StatableInterface::STATE_DISABLED);
    }

    public function disable(): bool
    {
        return $this->update(StatableInterface::STATE_DISABLED);
    }
}
