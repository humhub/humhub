<?php

namespace humhub\components;

use humhub\helpers\DatabaseHelper;
use Yii;
use yii\base\BaseObject;
use yii\base\StaticInstanceInterface;
use yii\base\StaticInstanceTrait;


final class InstallationState extends BaseObject implements StaticInstanceInterface
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
        try {
            $this->state = (int)Yii::$app->settings->get(self::class, null);
        } catch (\Exception $e) {
            // Database seems not working
        }

        if ($this->state === self::STATE_INSTALLED) {
            ;
        } elseif (empty(Yii::$app->db->dsn) || empty(Yii::$app->db->username)) {
            $this->state = self::STATE_NOT_INSTALLED;
        } elseif ($this->isDatabaseInstalled()) {
            $this->state = self::STATE_DATABASE_CREATED;
        } else {
            $this->state = self::STATE_DATABASE_CONFIGURED;
        }
    }

    public function setInstalled(): void
    {
        $this->setState(self::STATE_INSTALLED);
    }

    private function setState(int $state): void
    {
        $this->state = $state;
        Yii::$app->settings->set(self::class, $this->state);
    }

    private function getState(): int
    {
        if ($this->state === self::STATE_NOT_INSTALLED) {
            $this->init();
        }

        return $this->state;
    }

    public function hasState(int $state): bool
    {
        return ($this->state >= $state);
    }

    private function isDatabaseInstalled(): bool
    {
        try {
            Yii::$app->db->open();
        } catch (\Exception $e) {
            DatabaseHelper::handleConnectionErrors($e);
            return false;
        }

        return in_array('setting', Yii::$app->db->schema->getTableNames());
    }
}
