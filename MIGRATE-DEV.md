Module Migration Guide
======================

See [humhub/documentation::docs/develop/modules-migrate.md](https://github.com/humhub/documentation/blob/master/docs/develop/modules-migrate.md)
for full version.

Version 1.15 (Unreleased)
-------------------------

### Behaviour change
- `\humhub\libs\BaseSettingsManager::deleteAll()` no longer uses the `$prefix` parameter as a full wildcard, but
  actually as a prefix. Use `$prefix = '%pattern%'` to get the old behaviour. Or use `$parameter = '%suffix'` if you
  want to match against the end of the names.
- `\humhub\libs\BaseSettingsManager::get()` now returns a pure int in case the (trimmed) value can be converted 


### Type restrictions
- `\humhub\libs\BaseSettingsManager` and its child classes on fields, method parameters, & return types
