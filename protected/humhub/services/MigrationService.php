<?php

/*
 * @link      https://www.humhub.org/
 * @copyright Copyright (c) 2023 HumHub GmbH & Co. KG
 * @license   https://www.humhub.com/licences
 */

namespace humhub\services;

use humhub\commands\MigrateController;
use humhub\components\Application;
use humhub\components\Event;
use humhub\components\Migration;
use humhub\components\Module;
use humhub\events\MigrationEvent;
use humhub\helpers\DataTypeHelper;
use humhub\interfaces\ApplicationInterface;
use humhub\modules\admin\models\forms\CacheSettingsForm;
use Throwable;
use uninstall;
use Yii;
use yii\base\ActionEvent;
use yii\base\Component;
use yii\base\Controller;
use yii\base\InvalidConfigException;
use yii\base\Module as BaseModule;
use yii\console\ExitCode;
use yii\db\Exception;
use yii\db\MigrationInterface;

/**
 * @since 1.16
 */
class MigrationService extends Component
{
    public const EVENT_AFTER_MIGRATION = 'afterMigration';
    protected const MIGRATION_NEW = 'new';
    protected const MIGRATION_UNINSTALL = 'uninstall';
    protected const MIGRATION_UP = 'up';

    protected BaseModule $module;
    private ?string $path;
    private ?int $lastMigrationResult = null;
    private ?string $lastMigrationOutput = null;

    public const DB_ACTION_CHECK = 0;
    public const DB_ACTION_RUN = 1;
    public const DB_ACTION_PENDING = 2;
    public const DB_ACTION_SESSION = 3;
    private const SESSION_LAST_MIGRATION_OUTPUT = 'DBLastMigrationOutput';

    /**
     * @param Module|ApplicationInterface|Application|null $module
     */
    public function __construct(?BaseModule $module = null)
    {
        DataTypeHelper::ensureClassType($module, [ApplicationInterface::class, Module::class, null]);

        $this->module = $module ?? Yii::$app;

        parent::__construct();
    }

    public function init()
    {
        parent::init();

        /**
         * Since for console application the id is set to 'humhub-console' and might be configured for the application too,
         * we need to use the hard-coded 'humhub' string for non-modules.
         *
         * @see \humhub\components\console\Application::$id
         * @see protected/humhub/config/console.php
         */
        $moduleId = $this->module instanceof Module
            ? $this->module->id
            : 'humhub';

        $this->path = "@$moduleId/migrations";

        $realpath = $this->getPath(true);

        if ($realpath === null || !is_dir($realpath)) {
            Yii::debug("Module has no migrations directory.", $this->module->id);
            $this->path = null;
        }
    }

    /**
     * @return Module|ApplicationInterface|Application|null
     */
    public function getModule(): BaseModule
    {
        return $this->module;
    }

    private function getPath(bool $resolve = false): ?string
    {
        if (!$resolve || $this->path === null) {
            return $this->path;
        }

        $path = realpath(Yii::getAlias($this->path));

        if ($path === false) {
            return null;
        }

        return $path;
    }

    public function getLastMigrationOutput(): ?string
    {
        return $this->lastMigrationOutput;
    }

    public function getLastMigrationResult(): ?int
    {
        return $this->lastMigrationResult;
    }

    public function hasMigrations(): bool
    {
        return $this->path !== null;
    }

    public function hasMigrationsPending(): bool
    {
        if (!$this->hasMigrations()) {
            return false;
        }

        if ($this->migrateNew() === false) {
            return false;
        }

        $migrationOutput = $this->getLastMigrationOutput();

        return !str_contains($migrationOutput, 'No new migrations found.');
    }

    public function getPendingMigrations(): array
    {
        return $this->runAction() === self::DB_ACTION_PENDING
        && preg_match_all('/(^|[\s\t]+)(m\d+.+)(\n|$)/', $this->getLastMigrationOutput(), $matches)
            ? $matches[2]
            : [];
    }

    /**
     * Run migrations.
     */
    public function migrateNew(): ?bool
    {
        return $this->migrate(MigrationService::MIGRATION_NEW);
    }

    /**
     * Check for pending migrations
     */
    public function migrateUp(): ?bool
    {
        return $this->migrate(MigrationService::MIGRATION_UP);
    }

    /**
     * @param string $action Must be MigrationService::MIGRATION_ACTION_UP to run migrations,
     * or MigrationService::MIGRATION_ACTION_NEW to check for pending migrations
     *
     * @return bool|null
     */
    private function migrate(string $action): ?bool
    {
        $result = $this->checkMigrationBefore($action);

        if ($result === null) {
            return null;
        }

        // this event is collecting the migration's result status and storing it in our event
        Event::on(
            MigrateController::class,
            Controller::EVENT_AFTER_ACTION,
            [
                $this,
                'onMigrationControllerAfterAction',
            ],
            $result,
        );

        // Disable max execution time to avoid timeouts during migrations
        @ini_set('max_execution_time', 0);

        $module = $this->getModule();

        ob_start();
        $controller = new MigrateController('migrate', $module, [
            'db' => Yii::$app->db,
            'interactive' => false,
            'color' => false,
            'migrationPath' => $this->getPath(),
            'includeModuleMigrations' => true,
        ]);

        /** @noinspection PhpUnhandledExceptionInspection */
        $controller->runAction($action);

        $result->output = ob_get_clean() ?: null;

        // we no longer need to listen to this event
        Event::off(
            MigrateController::class,
            Controller::EVENT_AFTER_ACTION,
            [
                $this,
                'onMigrationControllerAfterAction',
            ],
        );

        return $this->checkMigrationStatus($result, $controller->getLastMigration());
    }

    /**
     * Catches migration results.
     *
     * @internal
     */
    public function onMigrationControllerAfterAction(ActionEvent $event)
    {
        if (!$event->sender instanceof MigrateController) {
            return;
        }

        if (!$event->data instanceof MigrationEvent || $event->data->sender !== $this) {
            return;
        }

        $event->data->result = $event->result ?? ExitCode::UNSPECIFIED_ERROR;
    }

    private function checkMigrationBefore(string $migrationAction): ?MigrationEvent
    {
        $this->lastMigrationOutput = null;
        $this->lastMigrationResult = null;

        if (!$this->hasMigrations()) {
            return null;
        }

        return new MigrationEvent([
            'sender' => $this,
            'module' => $this->getModule(),
            'migration' => $migrationAction,
        ]);
    }

    /**
     * @param MigrationEvent $result
     *
     * @return bool
     * @throws InvalidConfigException
     * @throws Throwable
     */
    private function checkMigrationStatus(MigrationEvent $result, ?MigrationInterface $migration): bool
    {
        $this->lastMigrationOutput = $result->output ?: 'Migration output unavailable';
        $this->lastMigrationResult = $result->result;

        /** @see \yii\console\controllers\BaseMigrateController::actionUp() */
        if ($result->result > ExitCode::OK) {
            Yii::error($this->lastMigrationOutput, $this->module->id);
        } else {
            Yii::info($this->lastMigrationOutput, $this->module->id);
        }

        $this->trigger(self::EVENT_AFTER_MIGRATION, $result);

        /** @see \yii\console\controllers\BaseMigrateController::actionUp() */
        if ($result->result > ExitCode::OK) {
            $errorMessage = "Migration failed!";
            $exception = null;

            if ($migration instanceof Migration && $exception = $migration->getLastException()) {
                $errorMessage .= "\n" . $exception->getMessage() . "\nSee application log for full trace.";
            } elseif (
                preg_match(
                    '@^Exception:\s+(?<message>.*?$)\s+(?<trace>.*?\{main\})$@ms',
                    $result->output ?? '',
                    $matches,
                )
            ) {
                $errorMessage .= "\n" . $matches['message'] . "\nSee application log for full trace.";
            }

            Yii::error($errorMessage, $this->module->id);

            if (YII_DEBUG) {
                throw new InvalidConfigException($errorMessage, 0, $exception);
            }

            return false;
        }

        return true;
    }

    public function uninstall(): ?bool
    {
        $result = $this->checkMigrationBefore(self::MIGRATION_UNINSTALL);

        if ($result === null) {
            return null;
        }

        $path = $this->getPath(true);
        $uninstallMigration = $path . '/uninstall.php';

        if (!file_exists($uninstallMigration)) {
            Yii::warning("Module has no uninstall migration!", $this->module->id);
            return null;
        }

        /**
         * Execute Uninstall Migration
         */
        ob_start();
        require_once($uninstallMigration);

        $migration = new uninstall();
        $migration->compact = false;

        try {
            $result->result = $migration->up() === false ? ExitCode::UNSPECIFIED_ERROR : ExitCode::OK;
        } catch (Exception $ex) {
            Yii::error($ex, $this->module->id);
            $result->result = ExitCode::UNSPECIFIED_ERROR;
        }
        $result->output = ob_get_clean();

        /**
         * Delete all Migration Table Entries
         */
        $migrations = opendir($path);
        $params = [];
        while (false !== ($filename = readdir($migrations))) {
            if ($filename === '.' || $filename === '..' || $filename === 'uninstall.php') {
                continue;
            }

            $command ??= Yii::$app->db->createCommand()->delete('migration', 'version = :version', $params);

            $version = str_replace('.php', '', $filename);
            $command->bindValue(':version', $version)->execute();
            $result->output .= "    > migration entry $version removed.\n";
        }

        return $this->checkMigrationStatus($result, $migration);
    }

    /**
     * @param Module|ApplicationInterface|Application|null $module
     *
     * @noinspection PhpDocMissingThrowsInspection
     */
    public static function create(?BaseModule $module = null): self
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        return Yii::createObject(static::class, [$module]);
    }

    /**
     * Store the last migration output in Session
     */
    private function storeLastMigrationOutput(): void
    {
        Yii::$app->session->set(self::SESSION_LAST_MIGRATION_OUTPUT, $this->getLastMigrationOutput());
    }

    /**
     * Restore the last migration output from Session
     */
    private function restoreLastMigrationOutput(): void
    {
        $this->lastMigrationOutput = Yii::$app->session->get(self::SESSION_LAST_MIGRATION_OUTPUT);
        Yii::$app->session->remove(self::SESSION_LAST_MIGRATION_OUTPUT);
    }

    /**
     * Run migration by requested action
     *
     * @param int $action Requested action
     * @return int Output action
     */
    public function runAction(int $action = self::DB_ACTION_CHECK): int
    {
        if ($action === self::DB_ACTION_RUN) {
            $this->migrateUp();
            $this->lastMigrationOutput .= "\n" . CacheSettingsForm::flushCache();
            $this->storeLastMigrationOutput();
            return self::DB_ACTION_SESSION;
        }

        // Try to restore last migration result from store(Sessions)
        $this->restoreLastMigrationOutput();

        if ($this->lastMigrationOutput === null) {
            return $this->hasMigrationsPending()
                ? self::DB_ACTION_PENDING
                : self::DB_ACTION_CHECK;
        }

        return self::DB_ACTION_RUN;
    }
}
