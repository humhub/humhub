<?php

/*
 * @link      https://www.humhub.org/
 * @copyright Copyright (c) 2023 HumHub GmbH & Co. KG
 * @license   https://www.humhub.com/licences
 */

namespace humhub\interfaces;

interface FilterableQueryInterface
{
    public const EVENT_WHERE_DEFAULT_FILTER = 'whereDefaultFilter';
    public const FILTER_CONTEXT_DEFAULT = 'default';
    public const FILTER_CONTEXT_INIT = 'init';

    /**
     * @param null|array $config = ['context' => 'name of the context']
     *
     * @return self
     */
    public function whereDefaultFilter(?array $config = null): self;

    /**
     * @param null|array $config = ['context' => 'name of the context']
     *
     * @return self
     */
    public function andWhereDefaultFilter(?array $config = null): self;
}
