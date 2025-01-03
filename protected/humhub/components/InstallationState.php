<?php

namespace humhub\components;

use humhub\helpers\DatabaseHelper;
use humhub\libs\DatabaseCredConfig;
use Yii;
use yii\base\BaseObject;
use yii\base\StaticInstanceInterface;
use yii\base\StaticInstanceTrait;

class InstallationState extends BaseObject implements StaticInstanceInterface
{
    use StaticInstanceTrait;

    /**
     * The application is not installed.
     * This state indicates that the installation process has not been started or completed.
     */
    public const STATE_NOT_INSTALLED = 0;

    /**
     * The database is configured.
     * This state indicates that the database configuration is complete and is valid, but the application may not be fully installed.
     */
    public const STATE_DATABASE_CONFIGURED = 1 << 1;

    /**
     * The application is fully installed.
     * This state indicates that the installation process is complete and the application is ready to use.
     */
    public const STATE_INSTALLED = self::STATE_DATABASE_CONFIGURED;

    private int $state;

    public function init()
    {
        if (!YII_ENV_TEST && !DatabaseCredConfig::exist()) {
            $this->state = self::STATE_NOT_INSTALLED;
        } else {
            $this->state = Yii::$app->settings->get(self::class, self::STATE_NOT_INSTALLED);
        }
    }

    public function setState(int $state): void
    {
        $this->state = $state;

        Yii::$app->settings->set(self::class, $this->state);
    }

    private function getState(): string
    {
        if ($this->state === self::STATE_NOT_INSTALLED) {
            $this->init();
        }

        return $this->state;
    }

    public function hasState(int $state): bool
    {
        return ($this->getState() & $state) === $state;
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
