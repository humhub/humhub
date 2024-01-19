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
 * Adds methods for schedulable records
 *
 * @since 1.16
 */
trait StateServiceSchedulableTrait
{
    /**
     * @since 1.14
     * @return bool
     */
    public function isScheduled(): bool
    {
        /** @var StateServiceInterface $this */
        return $this->is(StatableInterface::STATE_SCHEDULED);
    }

    /**
     * @since 1.14
     * @return bool
     */
    public function schedule(?string $date): bool
    {
        /** @var StateServiceInterface $this */
        return $this->update(StatableInterface::STATE_SCHEDULED, ['scheduled_at' => $date]);
    }
}
