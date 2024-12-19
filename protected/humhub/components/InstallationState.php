<?php

namespace humhub\components;

use Yii;
use yii\base\BaseObject;
use yii\base\StaticInstanceInterface;
use yii\base\StaticInstanceTrait;

class InstallationState extends BaseObject implements StaticInstanceInterface
{
    use StaticInstanceTrait;

    public const STATE_NOT_INSTALLED = 1 << 0;
    public const STATE_INSTALLED = 1 << 1;

    private int $state;

    public function init()
    {
        $this->state = Yii::$app->settings->get(self::class, self::STATE_NOT_INSTALLED);
    }

    public function setState(int $state): void
    {
        $this->state |= $state;

        Yii::$app->settings->set(self::class, $this->state);
    }

    public function getState(): string
    {
        return $this->state;
    }

    public function hasState(int $state): bool
    {
        return ($this->state & $state) === $state;
    }
}
