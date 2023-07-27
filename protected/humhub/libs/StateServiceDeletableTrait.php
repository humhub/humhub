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
trait StateServiceDeletableTrait
{
    public function isDeleted(): bool
    {
        return $this->is(StatableInterface::STATE_DELETED);
    }

    public function delete(): bool
    {
        return $this->update(StatableInterface::STATE_DELETED);
    }
}
