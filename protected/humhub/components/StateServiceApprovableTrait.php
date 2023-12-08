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
 * Adds methods for approvable records
 *
 * @since 1.16
 */
trait StateServiceApprovableTrait
{
    public function isNeedingApproval(): bool
    {
        /** @var StateServiceInterface $this */
        return $this->is(StatableInterface::STATE_NEEDS_APPROVAL);
    }

    public function requireApproval(): bool
    {
        /** @var StateServiceInterface $this */
        return $this->update(StatableInterface::STATE_NEEDS_APPROVAL);
    }
}
