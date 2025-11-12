<?php

namespace humhub\helpers;

use yii\base\StaticInstanceInterface;
use yii\base\StaticInstanceTrait;

class ConfigHelper implements StaticInstanceInterface
{
    use StaticInstanceTrait;

    public const SET_HUMHUB  = 1 << 0; // 1
    public const SET_DYNAMIC = 1 << 1; // 2
    public const SET_COMMON  = 1 << 2; // 4
    public const SET_ENV     = 1 << 3; // 8

    private $humhub = [];
    private $dynamic = [];
    private $common = [];
    private $env = [];

    public function setHumhub(array ...$config): static
    {
        $this->humhub = $config;

        return $this;
    }

    public function setDynamic(array ...$config): static
    {
        $this->dynamic = $config;

        return $this;
    }

    public function setCommon(array ...$config): static
    {
        $this->common = $config;

        return $this;
    }

    public function setEnv(array ...$config): static
    {
        $this->env = $config;

        return $this;
    }

    public function getCommon(): array
    {
        return $this->common;
    }

    public function getDynamic(): array
    {
        return $this->dynamic;
    }

    public function getEnv(): array
    {
        return $this->env;
    }

    public function getHumhub(): array
    {
        return $this->humhub;
    }

    public function toArray(int $flags = null)
    {
        if ($flags === null) {
            $flags = self::SET_HUMHUB | self::SET_DYNAMIC | self::SET_COMMON | self::SET_ENV;
        }

        return ArrayHelper::merge(
            ...($flags & self::SET_HUMHUB ? $this->humhub : []),
            ...($flags & self::SET_DYNAMIC ? $this->dynamic : []),
            ...($flags & self::SET_COMMON ? $this->common : []),
            ...($flags & self::SET_ENV ? $this->env : []),
        );
    }

    public function get($key, int $flags = null)
    {
        return ArrayHelper::getValue($this->toArray($flags), $key);
    }
}
