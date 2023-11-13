HumHub Changelog
================

1.16.0 (Unreleased)
-------------------
- Fix #6636: Module Manager test
- Enh #6530: Small performance improvements
- Fix #6511: Only test compatible modules in `onMarketplaceAfterFilterModules()`
- Enh #6511: Backup folder path is now return from `removeModule()`
- Fix #6511: `canRemoveModule` no longer throws an Exception
- Enh #6511: Allow an empty filter list to filter all registered modules
- Enh #6511: Allow module paths for `enableModules()`
- Enh #6511: Verify module's event definition
- Enh #6511: Make module's module.json keywords accessible and searchable
- Enh #6511: Add Event tracking capabilities to HumHubDbTestCase
- Enh #6511: Add test for ModuleManager
- Fix #6519: Ensure e-mails would always have a sender address set
- Enh #6512: Show error messages when DB connection configuration is invalid
- Enh #5315: Default stream sort by `created_at` instead of `id`
- Fix #6337: Update `created_at` after first publishing of content record
- Fix #6631: Fix visibility of the method `Controller::getAccessRules()`
- Enh #6650: Add assets GZIP compression with Apache
