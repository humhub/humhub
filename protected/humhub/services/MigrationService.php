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
use humhub\components\Module;
use humhub\events\MigrationEvent;
use humhub\helpers\DataTypeHelper;
use humhub\interfaces\ApplicationInterface;
use Throwable;
use Yii;
use yii\base\ActionEvent;
use yii\base\Component;
use yii\base\Controller;
use yii\base\InvalidConfigException;
use yii\base\Module as BaseModule;
use yii\console\ExitCode;

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

    /**
     * @param Module|ApplicationInterface|Application|null $module
     */
    public function __construct(?BaseModule $module = null)
    {
        DataTypeHelper::filterClassType($module, [ApplicationInterface::class, Module::class, null]);

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
                'onMigrationControllerAfterAction'
            ],
            $result
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
                'onMigrationControllerAfterAction'
            ]
        );

        return $this->checkMigrationStatus($result);
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
    private function checkMigrationStatus(MigrationEvent $result): bool
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

            if (YII_DEBUG) {
                throw new InvalidConfigException($errorMessage);
            }

            Yii::error($errorMessage, $this->module->id);

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

        $migration = new \uninstall();
        $migration->compact = false;

        try {
            $result->result = $migration->up() === false ? ExitCode::UNSPECIFIED_ERROR : ExitCode::OK;
        } catch (\yii\db\Exception $ex) {
            Yii::error($ex, $this->module->id);
            $result->result = ExitCode::UNSPECIFIED_ERROR;
        }
        $result->output = ob_get_clean();

        /**
         * Delete all Migration Table Entries
         */
        $migrations = opendir($path);
        $params = [];
        while (false !== ($migration = readdir($migrations))) {
            if ($migration === '.' || $migration === '..' || $migration === 'uninstall.php') {
                continue;
            }

            $command ??= Yii::$app->db->createCommand()->delete('migration', 'version = :version', $params);

            $version = str_replace('.php', '', $migration);
            $command->bindValue(':version', $version)->execute();
            $result->output .= "    > migration entry $version removed.\n";
        }

        return $this->checkMigrationStatus($result);
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
}
