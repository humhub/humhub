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

    private ?int $state = null;

    private ?\Throwable $databaseConnectionError = null;

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

    /**
     * Whether the database is configured but currently not reachable.
     *
     * When this is true the real installation state cannot be determined, so the
     * caller must not fall back to a lower state that would present the installer
     * (which would offer to set up an already installed instance during a
     * transient database outage).
     *
     * @since 1.19
     */
    public function isDatabaseUnreachable(): bool
    {
        return $this->databaseConnectionError !== null;
    }

    /**
     * @return \Throwable|null the exception raised while connecting to the configured database, if any
     * @since 1.19
     */
    public function getDatabaseConnectionError(): ?\Throwable
    {
        return $this->databaseConnectionError;
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
        return !empty(Yii::$app->db->dsn) && !empty(Yii::$app->db->username);
    }

    private function isDatabaseInstalled(): bool
    {
        try {
            Yii::$app->db->open();
        } catch (\Exception $e) {
            // A reachable server that merely lacks the configured database
            // (fresh install, schema not created yet) is a not-yet-installed
            // state, not an outage — only a genuinely unreachable server is
            // recorded so the caller keeps redirecting to the installer here.
            if (!$this->isMissingDatabaseError($e)) {
                $this->databaseConnectionError = $e;
            }
            return false;
        }

        return in_array('setting', Yii::$app->db->schema->getTableNames());
    }

    /**
     * Whether a database connection error means the configured database does
     * not exist (or is not accessible) rather than the server being unreachable.
     * The server responded in this case, so it is reachable — e.g. a fresh
     * Docker install whose database has not been created yet.
     */
    private function isMissingDatabaseError(\Throwable $e): bool
    {
        // MySQL: 1049 = unknown database, 1044 = access denied for the database.
        $code = $e instanceof \yii\db\Exception ? ($e->errorInfo[1] ?? $e->getCode()) : $e->getCode();

        return in_array((int)$code, [1049, 1044], true);
    }
}
