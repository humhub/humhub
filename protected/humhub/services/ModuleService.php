<?php

/*
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\services;

use humhub\components\Module;
use humhub\components\ModuleEvent;
use humhub\components\ModuleManager;
use humhub\models\ModuleEnabled;
use humhub\modules\marketplace\Module as MarketplaceModule;
use Yii;
use yii\base\ErrorException;
use yii\base\Exception;
use yii\helpers\FileHelper;
use yii\web\ServerErrorHttpException;

/**
 * ModuleService handles lifecycle and removal operations for a single module instance.
 *
 * Obtain an instance via the module:
 * ```php
 * $module->getModuleService()->canRemove();
 * $module->getModuleService()->remove();
 * ```
 *
 * **Extension point for module authors:** override {@see Module::enable()} and
 * {@see Module::disable()} in your Module class — not the methods here. The `enable()`
 * and `disable()` methods on this service are `@internal` building blocks called by
 * `Module::enable()` / `Module::disable()` and are not part of the public API.
 *
 * @since 1.19
 */
class ModuleService
{
    public function __construct(private readonly Module $module)
    {
    }

    /**
     * Registers the module as enabled: saves the DB record, updates in-memory state,
     * re-registers the module config, flushes the module cache, and fires the enable
     * events on ModuleManager.
     *
     * This is the **registration step only** — it does not run database migrations.
     * Call {@see Module::enable()} for the full enable flow including migrations.
     *
     * To add custom logic at enable time, override {@see Module::enable()} in your
     * Module class rather than this method.
     *
     * @internal called by {@see Module::enable()}
     * @throws ServerErrorHttpException if module requirements are not met
     */
    public function enable(): void
    {
        $this->checkRequirements();

        $moduleManager = $this->getModuleManager();

        $moduleManager->trigger(
            ModuleManager::EVENT_BEFORE_MODULE_ENABLE,
            new ModuleEvent(['module' => $this->module]),
        );

        if (!ModuleEnabled::findOne(['module_id' => $this->module->id])) {
            (new ModuleEnabled([
                'module_id' => $this->module->id,
                'version' => $this->module->version,
            ]))->save();
        }

        $moduleManager->markAsEnabled($this->module->id);
        $moduleManager->register($this->module->getBasePath());
        $moduleManager->flushCache();

        $moduleManager->trigger(
            ModuleManager::EVENT_AFTER_MODULE_ENABLE,
            new ModuleEvent(['module' => $this->module]),
        );
    }

    /**
     * Deregisters the module: removes the DB record, updates in-memory state, unsets the module
     * from the application, flushes the module cache, and fires the disable events on ModuleManager.
     *
     * This is the **deregistration step only** — it does not run uninstall migrations or clean up
     * module data. Call {@see Module::disable()} for the full disable flow.
     *
     * To add custom cleanup logic at disable time, override {@see Module::disable()} in your
     * Module class rather than this method.
     *
     * @internal called by {@see Module::disable()}
     */
    public function disable(): void
    {
        $moduleManager = $this->getModuleManager();

        $moduleManager->trigger(
            ModuleManager::EVENT_BEFORE_MODULE_DISABLE,
            new ModuleEvent(['module' => $this->module]),
        );

        $moduleEnabled = ModuleEnabled::findOne(['module_id' => $this->module->id]);
        if ($moduleEnabled !== null) {
            $moduleEnabled->delete();
        }

        $moduleManager->markAsDisabled($this->module->id);
        Yii::$app->setModule($this->module->id, null);
        $moduleManager->flushCache();

        $moduleManager->trigger(
            ModuleManager::EVENT_AFTER_MODULE_DISABLE,
            new ModuleEvent(['module' => $this->module]),
        );
    }

    /**
     * Returns true if the module can be removed (i.e. is located in the marketplace module directory).
     */
    public function canRemove(): bool
    {
        /** @var MarketplaceModule|null $marketplaceModule */
        $marketplaceModule = Yii::$app->getModule('marketplace');
        if ($marketplaceModule === null) {
            return false;
        }

        $modulePath = FileHelper::normalizePath($this->module->getBasePath());
        $marketplacePath = FileHelper::normalizePath(realpath(Yii::getAlias($marketplaceModule->modulesPath)));

        return str_contains($modulePath, $marketplacePath);
    }

    /**
     * Removes the module directory, optionally disabling it first.
     *
     * When {@see ModuleManager::$createBackup} is enabled (default), a backup copy is created
     * under `@runtime/module_backups/` before deletion.
     *
     * @return string|null path to the backup directory, or null if no backup was created
     * @throws Exception
     * @throws ErrorException
     */
    public function remove(bool $disableBeforeRemove = true): ?string
    {
        if ($disableBeforeRemove && Yii::$app->hasModule($this->module->id)) {
            $this->module->disable();
        }

        $moduleManager = $this->getModuleManager();

        if ($moduleManager->createBackup) {
            $backupFolder = Yii::getAlias('@runtime/module_backups');
            FileHelper::createDirectory($backupFolder);

            $backupPath = $backupFolder . DIRECTORY_SEPARATOR . $this->module->id . '_' . time();
            FileHelper::copyDirectory($this->module->getBasePath(), $backupPath);
            FileHelper::removeDirectory($this->module->getBasePath());
        } else {
            $backupPath = null;
            // TODO: Delete directory
        }

        $moduleManager->flushCache();

        return $backupPath;
    }

    /**
     * Checks whether the module's requirements are satisfied.
     *
     * @throws ServerErrorHttpException if a requirement is not met
     */
    public function checkRequirements(): void
    {
        $requirementsPath = $this->module->getBasePath() . DIRECTORY_SEPARATOR . 'requirements.php';
        if (!file_exists($requirementsPath)) {
            return;
        }

        $result = include $requirementsPath;

        if (is_string($result)) {
            Yii::error('Error enabling the "' . $this->module->id . '" module: ' . $result, 'module');
            throw new ServerErrorHttpException($result);
        }
    }

    private function getModuleManager(): ModuleManager
    {
        return Yii::$app->moduleManager;
    }
}
