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

            return in_array('setting', Yii::$app->db->schema->getTableNames());
        } catch (\Exception $e) {
            // A server that responds but reports the configured database or
            // credentials are not (yet) usable is a not-yet-installed / partially
            // configured state (fresh or incomplete install) — keep redirecting
            // to the installer. Only a genuinely unreachable or unavailable
            // server is recorded as an outage, so the caller returns a 503.
            if (!$this->isDatabaseSetupError($e)) {
                $this->databaseConnectionError = $e;
            }

            return false;
        }
    }

    /**
     * Whether a database error means the server is reachable but the configured
     * database/credentials are not (yet) set up — as opposed to the server being
     * unreachable or unavailable. Such errors indicate a fresh or incompletely
     * configured install (e.g. a Docker install whose database was not created
     * yet, or a DSN missing the database name), not an outage of an installed
     * instance, so the installer must stay reachable.
     */
    private function isDatabaseSetupError(\Throwable $e): bool
    {
        // Native MySQL server-response codes (read from errorInfo, since getCode()
        // may carry a SQLSTATE string for statement-level errors):
        //   1049 unknown database        1046 no database selected (missing dbname)
        //   1044 no access to database   1045 / 1698 access denied (authentication)
        $code = (int)($e instanceof \yii\db\Exception ? ($e->errorInfo[1] ?? $e->getCode()) : $e->getCode());

        return in_array($code, [1044, 1045, 1046, 1049, 1698], true);
    }
}
