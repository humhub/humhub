<?php

/*
 * @link      https://www.humhub.org/
 * @copyright Copyright (c) 2023 HumHub GmbH & Co. KG
 * @license   https://www.humhub.com/licences
 */

namespace humhub\tests\codeception\unit\components;

use humhub\components\StateService;
use humhub\interfaces\FilterableQueryInterface;
use humhub\interfaces\StatableInterface;

class StateServiceMock extends StateService
{
    private bool $initStatesCalled = false;

    public function initStates(): self
    {
        $this->initStatesCalled = true;

        $this->allowState(StatableInterface::STATE_DRAFT);
        $this->allowState(StatableInterface::STATE_PUBLISHED);
        $this->allowState(StatableInterface::STATE_DELETED);

        $this->defaultQueriedStates = [
            FilterableQueryInterface::FILTER_CONTEXT_DEFAULT => [StatableInterface::STATE_PUBLISHED,],
        ];

        return parent::initStates();
    }

    /**
     * @return bool
     */
    public function isInitStatesCalled(): bool
    {
        return $this->initStatesCalled;
    }
}
