<?php

namespace humhub\components;

use Yii;
use yii\base\BaseObject;
use yii\base\InvalidConfigException;
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

    private ?int $state;

    public function init()
    {
        if (!$this->isDatabaseConfigured()) {
            return $this->state = self::STATE_NOT_INSTALLED;
        }

        $this->state = Yii::$app->settings->get(self::class);

        if (is_null($this->state)) {
            $this->state = self::STATE_DATABASE_CONFIGURED;

            if ($this->isDatabaseInstalled()) {
                $this->state = self::STATE_DATABASE_CREATED;
            }
        } elseif (intval($this->state) !== self::STATE_INSTALLED) {
            throw new InvalidConfigException('Invalid installation state: ' . $this->state);
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

    private function isDatabaseConfigured(): bool
    {
        return (!empty(Yii::$app->db->dsn) && !empty(Yii::$app->db->username));
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
