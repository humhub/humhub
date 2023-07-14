<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2023 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\services;

use humhub\components\StateService;
use humhub\interfaces\StatableInterface;
use humhub\libs\StateServiceApprovableTrait;
use humhub\libs\StateServiceEnablableTrait;
use humhub\libs\StateServiceSoftDeletableTrait;
use humhub\modules\content\models\Content;
use yii\db\ActiveRecord;

/**
 * This service is used to extend Content record for state features
 * @since 1.14
 */
class UserStateService extends StateService
{
    use StateServiceEnablableTrait;
    use StateServiceSoftDeletableTrait;
    use StateServiceApprovableTrait;

    /**
     * @var Content
     */
    public ActiveRecord $record;

    public function initStates(): self
    {
        $this->allowState(StatableInterface::STATE_DISABLED, 'disabled');
        $this->allowState(StatableInterface::STATE_ENABLED, 'enabled');
        $this->allowState(StatableInterface::STATE_NEEDS_APPROVAL, 'need approval');
        $this->allowState(StatableInterface::STATE_SOFT_DELETED, 'deleted');

        // return all users by default
        $this->defaultQueriedStates = null;

        return parent::initStates();
    }
}
