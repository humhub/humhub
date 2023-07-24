<?php

/*
 * @link      https://www.humhub.org/
 * @copyright Copyright (c) 2023 HumHub GmbH & Co. KG
 * @license   https://www.humhub.com/licences
 */

namespace humhub\libs;

use humhub\interfaces\StatableInterface;

/**
 * Adds methods for archivable records
 *
 * @since 1.16
 */
trait StateServiceArchiveableTrait
{
    public function isArchiveable(): bool
    {
        return $this->is(StatableInterface::STATE_ARCHIVED);
    }

    public function archive(): bool
    {
        return $this->update(StatableInterface::STATE_ARCHIVED);
    }
}
