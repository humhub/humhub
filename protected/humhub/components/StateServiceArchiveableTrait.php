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
 * Adds methods for archivable records
 *
 * @since 1.16
 */
trait StateServiceArchiveableTrait
{
    public function isArchiveable(): bool
    {
        /** @var StateServiceInterface $this */
        return $this->is(StatableInterface::STATE_ARCHIVED);
    }

    public function archive(): bool
    {
        /** @var StateServiceInterface $this */
        return $this->update(StatableInterface::STATE_ARCHIVED);
    }
}
