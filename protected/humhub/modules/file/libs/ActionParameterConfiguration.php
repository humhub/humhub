<?php

/*
 * @link      https://www.humhub.org/
 * @copyright Copyright (c) 2023 HumHub GmbH & Co. KG
 * @license   https://www.humhub.com/licences
 */

namespace humhub\modules\file\libs;

use ReflectionNamedType;
use ReflectionParameter;

class ActionParameterConfiguration
{
    public string $name;
    public bool $isArray;
    public string $typeName;
    public bool $allowsNull;
    public bool $isDefaultValueAvailable;

    /**
     * @var mixed
     */
    public $defaultValue;
    public bool $isVariadic;

    /** @noinspection PhpVoidFunctionResultUsedInspection */
    public function __construct(ReflectionParameter $param)
    {
        $this->name = $param->getName();

        $type = $param->getType();

        $this->isArray = PHP_VERSION_ID >= 80000
            ? $type instanceof ReflectionNamedType && $type->getName() === 'array'
            : $param->isArray();

        $this->typeName   = $type->getName();
        $this->allowsNull = $type->allowsNull();

        if ($this->isDefaultValueAvailable = $param->isDefaultValueAvailable()) {
            $this->defaultValue = $param->getDefaultValue();
        }

        $this->isVariadic = $param->isVariadic();
    }
}
