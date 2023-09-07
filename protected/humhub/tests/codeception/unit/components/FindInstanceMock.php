<?php

/*
 * @link      https://www.humhub.org/
 * @copyright Copyright (c) 2023 HumHub GmbH & Co. KG
 * @license   https://www.humhub.com/licences
 */

namespace humhub\tests\codeception\unit\components;

use humhub\components\FindInstanceTrait;
use humhub\helpers\RuntimeCacheHelper;
use humhub\interfaces\FindInstanceInterface;

class FindInstanceMock implements FindInstanceInterface
{
    use FindInstanceTrait {
        findInstance as public;
        validateInstanceIdentifier as public;
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
        $this->args += $criteria;
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

    public function getUniqueId(): string
    {
        return RuntimeCacheHelper::normaliseObjectIdentifier($this, $this->args);
    }
}
