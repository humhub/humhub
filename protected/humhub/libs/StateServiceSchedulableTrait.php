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
trait StateServiceSchedulableTrait
{
    public function isScheduled(): bool
    {
        return $this->is(StatableInterface::STATE_SCHEDULED);
    }

    public function schedule(?string $date): bool
    {
        return $this->update(StatableInterface::STATE_SCHEDULED, ['scheduled_at' => $date]);
    }
}
