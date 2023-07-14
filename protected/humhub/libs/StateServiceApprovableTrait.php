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
trait StateServiceApprovableTrait
{
    public function isNeedingApproval(): bool
    {
        return $this->is(StatableInterface::STATE_NEEDS_APPROVAL);
    }

    public function requireApproval(): bool
    {
        return $this->update(StatableInterface::STATE_NEEDS_APPROVAL);
    }
}
