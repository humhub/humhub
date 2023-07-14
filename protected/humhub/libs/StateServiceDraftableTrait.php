<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2023 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\libs;

use humhub\interfaces\StatableInterface;

/**
 * Adds methods for draftable records
 *
 * @since 1.15
 */
trait StateServiceDraftableTrait
{
    public function isDraft(): bool
    {
        return $this->is(StatableInterface::STATE_DRAFT);
    }

    public function draft(): bool
    {
        return $this->update(StatableInterface::STATE_DRAFT);
    }
}
