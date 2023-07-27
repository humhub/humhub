<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2023 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\file\services;

use humhub\components\StateService;
use humhub\interfaces\StatableInterface;
use humhub\libs\StateServiceDeletableTrait;
use humhub\libs\StateServiceDraftableTrait;
use humhub\libs\StateServicePublishableTrait;
use humhub\libs\StateServiceSchedulableTrait;
use humhub\modules\content\models\Content;

/**
 * This service is used to extend Content record for state features
 * @since 1.14
*
 * @property Content $record
 */
class FileStateService extends StateService
{
    use StateServiceDeletableTrait;
    use StateServicePublishableTrait;
    use StateServiceDraftableTrait;

    public function initStates(): self
    {
        $this->allowState(StatableInterface::STATE_PUBLISHED, 'published');
        $this->allowState(StatableInterface::STATE_DRAFT, 'draft');
        $this->allowState(StatableInterface::STATE_DELETED, 'deleted');

        $this->defaultQueriedStates = [
            StatableInterface::STATE_PUBLISHED,
        ];

        return parent::initStates();
    }
}
