<?php

/*
 * @link      https://www.humhub.org/
 * @copyright Copyright (c) 2023 HumHub GmbH & Co. KG
 * @license   https://www.humhub.com/licences
 */

namespace humhub\modules\space\components;

use humhub\components\StateService;
use humhub\interfaces\FilterableQueryInterface;
use humhub\interfaces\StatableInterface;
use humhub\libs\StateServiceArchiveableTrait;
use humhub\libs\StateServiceEnablableTrait;
use humhub\libs\StateServiceSoftDeletableTrait;
use humhub\modules\content\models\Content;
use yii\db\ActiveRecord;

/**
 * This service is used to extend Content record for state features
 * @since 1.16
 */
class SpaceStateService extends StateService
{
    use StateServiceEnablableTrait;
    use StateServiceSoftDeletableTrait;
    use StateServiceArchiveableTrait;

    /**
     * @var Content
     */
    public ActiveRecord $record;
    public string $field = 'status';

    public function initStates(): self
    {
        $this->allowState(StatableInterface::STATE_DISABLED);
        $this->allowState(StatableInterface::STATE_ENABLED);
        $this->allowState(StatableInterface::STATE_ARCHIVED);

        $this->defaultQueriedStates = [
            FilterableQueryInterface::FILTER_CONTEXT_DEFAULT => [StatableInterface::STATE_ENABLED, ],
        ];

        return parent::initStates();
    }
}
