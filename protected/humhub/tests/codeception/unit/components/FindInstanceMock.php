<?php

/*
 * @link      https://www.humhub.org/
 * @copyright Copyright (c) 2023 HumHub GmbH & Co. KG
 * @license   https://www.humhub.com/licences
 */

namespace humhub\tests\codeception\unit\components;

use humhub\components\FindInstanceTrait;
use humhub\interfaces\FindInstanceInterface;

class FindInstanceMock implements FindInstanceInterface
{
    use FindInstanceTrait {
        findInstance as _FindInstanceTrait_findInstance;
    }

    public array $args;

    public function __construct(...$args)
    {
        $this->args = $args;
    }

    public static function find()
    {
        return new static();
    }

    public function where($criteria)
    {
        $this->args[] = $criteria;
        return $this;
    }

    public function one()
    {
        return $this;
    }

    public static function findOne($condition): ?self
    {
        return new static($condition);
    }

    public static function findInstance($identifier, ?array $config = [], ?iterable $simpleCondition = null): ?self
    {
        return static::_FindInstanceTrait_findInstance($identifier, $config, $simpleCondition);
    }
}
