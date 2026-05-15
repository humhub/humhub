# Module Lifecycle

A module passes through four states — installed, enabled, disabled, uninstalled — controlled by the `ModuleManager`. This page covers what each transition actually does, and which hooks fire.

## Installed

A module is *installed* once its directory sits in one of the [module autoload paths](intro-environment.md#module-loader-path) (default: `protected/modules`). Installation is purely filesystem — no DB writes happen yet.

You can extend the autoload paths via the `moduleAutoloadPaths` parameter. See [development environment → module loader path](intro-environment.md#module-loader-path).

## Bootstrap

On every request the `humhub\components\bootstrap\ModuleAutoLoader` scans the autoload paths, picks up enabled modules, and attaches the [event listeners](concept-events.md) declared in each module's `config.php`. Disabled modules are skipped here — their event handlers don't run.

## Enabling

A module starts inert. To make it do anything, enable it via:

- *Administration → Modules* in the admin UI
- `php yii module/enable <module-id>` on the console

Enabling will:

1. run all of the module's [database migrations](concept-models.md#initial-migration)
2. insert a row into `module_enabled`

The `ModuleManager` fires:

- `ModuleManager::EVENT_BEFORE_MODULE_ENABLE`
- `ModuleManager::EVENT_AFTER_MODULE_ENABLE`

[`ContentContainerModule`](module-base-class.md#contentcontainermodule) instances additionally need to be enabled on a per-space or per-user basis, in that container's module management section.

## Disabling

Disabling drops module-specific data and detaches the module from the bootstrap process:

- *Administration → Modules*
- `php yii module/disable <module-id>`

The `ModuleManager` fires:

- `ModuleManager::EVENT_BEFORE_MODULE_DISABLE`
- `ModuleManager::EVENT_AFTER_MODULE_DISABLE`

The default `Module::disable()` implementation:

- runs `migrations/uninstall.php` (drop tables / columns)
- clears all `ContentContainerSettings` and global `Settings` belonging to the module
- removes the `module_enabled` row

`ContentContainerModule` adds per-container cleanup. See [`Module::disable()`](module-base-class.md#disable) and [`ContentContainerModule::disableContentContainer()`](module-base-class.md#disablecontentcontainer) for hooks where you delete your own module data.

## Uninstalling

Uninstalling = removing the module directory from the autoload path. The marketplace UI does this for you. **Disable the module first** — deleting an enabled module folder leaves orphaned tables and settings that the disable hook would normally clean up.
