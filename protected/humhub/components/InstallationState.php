<?php

namespace humhub\components;

use humhub\helpers\DatabaseHelper;
use Yii;
use yii\base\BaseObject;
use yii\base\StaticInstanceInterface;
use yii\base\StaticInstanceTrait;

class InstallationState extends BaseObject implements StaticInstanceInterface
{
    use StaticInstanceTrait;

    public const STATE_NOT_INSTALLED = 0;
    public const STATE_DATABASE_CONFIGURED = 1 << 1;
    public const STATE_INSTALLED = self::STATE_DATABASE_CONFIGURED;

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

    public function isDatabaseInstalled(): bool
    {
        $configExist = $this->hasState(self::STATE_DATABASE_CONFIGURED);

        if (!$configExist) {
            return false;
        }

        try {
            Yii::$app->db->open();
        } catch (\Exception $e) {
            if ($configExist) {
                DatabaseHelper::handleConnectionErrors($e);
            }
            return false;
        }

        return in_array('setting', Yii::$app->db->schema->getTableNames());
    }
}
