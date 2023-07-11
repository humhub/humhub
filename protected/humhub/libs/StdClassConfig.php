<?php

/*
 * @link      https://www.humhub.org/
 * @copyright Copyright (c) 2023 HumHub GmbH & Co. KG
 * @license   https://www.humhub.com/licences
 */

namespace humhub\libs;

use ReflectionObject;

class StdClassConfig
{
    /**
     * @var mixed|null
     */
    public $default;
    public ReflectionObject $reflection;
    public bool $loading = true;

    public function __construct(StdClass $parent)
    {
        $this->reflection = new ReflectionObject($parent);
    }
}
