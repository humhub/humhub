Module Migration Guide
======================

See [humhub/documentation::docs/develop/modules-migrate.md](https://github.com/humhub/documentation/blob/master/docs/develop/modules-migrate.md)
for full version.

Version 1.15 (Unreleased)
-------------------------

- `\humhub\libs\BaseSettingsManager::deleteAll()` no longer uses the `$prefix` parameter as a full wildcard, but
  actually as a prefix. Use `$prefix = '%pattern%'` to get the old behaviour. Or use `$parameter = '%suffix'` if you
  want to match against the end of the names.
