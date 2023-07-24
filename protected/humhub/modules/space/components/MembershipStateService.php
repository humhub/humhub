<?php

/*
 * @link      https://www.humhub.org/
 * @copyright Copyright (c) 2023 HumHub GmbH & Co. KG
 * @license   https://www.humhub.com/licences
 */

namespace humhub\modules\space\components;

use humhub\components\StateService;
use humhub\interfaces\FilterableQueryInterface;
use humhub\libs\StateServiceArchiveableTrait;
use humhub\libs\StateServiceEnablableTrait;
use humhub\libs\StateServiceSoftDeletableTrait;
use humhub\modules\content\models\Content;
use humhub\modules\space\models\Membership;
use yii\db\ActiveRecord;

/**
 * This service is used to extend Content record for state features
 * @since 1.16
 */
class MembershipStateService extends StateService
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
        $this->allowState(Membership::STATE_INVITED);
        $this->allowState(Membership::STATE_APPLICANT);
        $this->allowState(Membership::STATE_MEMBER);

        $this->defaultQueriedStates = [
            FilterableQueryInterface::FILTER_CONTEXT_DEFAULT => [Membership::STATE_MEMBER, ],
        ];

        return parent::initStates();
    }
}
