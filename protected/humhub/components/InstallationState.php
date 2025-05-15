<?php

namespace humhub\components;

use humhub\libs\DynamicConfig;
use Yii;
use yii\base\BaseObject;
use yii\base\StaticInstanceInterface;
use yii\base\StaticInstanceTrait;

class InstallationState extends BaseObject implements StaticInstanceInterface
{
    use StaticInstanceTrait;

    /**
     * The application is not installed.
     * Condition: No database configuration is present.
     */
    public const STATE_NOT_INSTALLED = 0;

    /**
     * The database is configured.
     * Condition: A database configuration is present.
     */
    public const STATE_DATABASE_CONFIGURED = 1;


    /**
     * The database is created.
     * Condition: The database has been migrated (e.g. `settings` table exists)
     */
    public const STATE_DATABASE_CREATED = 2;

    /**
     * The database is initialized.
     * Condition: The admin user is created and the installation is complete.
     */
    public const STATE_INSTALLED = 3;

    private int $state;

    public function init()
    {
        if (!YII_ENV_TEST && !DynamicConfig::exist()) {
            return $this->state = self::STATE_NOT_INSTALLED;
        }

        $this->state = Yii::$app->settings->get(self::class, self::STATE_NOT_INSTALLED);

        if ($this->state === self::STATE_NOT_INSTALLED) {
            if (DynamicConfig::exist() && !empty(Yii::$app->db->dsn) && !empty(Yii::$app->db->username)) {
                $this->state = self::STATE_DATABASE_CONFIGURED;

                if ($this->isDatabaseInstalled()) {
                    $this->state = self::STATE_DATABASE_CREATED;
                }
            }
        } elseif ($this->state >= self::STATE_DATABASE_CREATED && !$this->isDatabaseInstalled()) {
            $this->state = self::STATE_DATABASE_CONFIGURED;
            if (empty(Yii::$app->db->dsn) || empty(Yii::$app->db->username)) {
                $this->state = self::STATE_NOT_INSTALLED;
            }
        }
    }

    private function setState(int $state): void
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
        return $this->getState() >= $state;
    }

    public function setInstalled(): void
    {
        $this->setState(self::STATE_INSTALLED);
    }

    public function setUninstalled(): void
    {
        Yii::$app->settings->delete(self::class);
        $this->init();
    }

    private function isDatabaseInstalled(): bool
    {
        try {
            Yii::$app->db->open();
        } catch (\Exception $e) {
            return false;
        }

        return in_array('setting', Yii::$app->db->schema->getTableNames());
    }
}
