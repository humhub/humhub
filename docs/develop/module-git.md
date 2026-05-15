# Module Git Repositories

Conventions for HumHub-operated module repositories on GitHub / GitLab. Custom modules outside HumHub's organisations can ignore this — it's a description of how the official modules are organised.

## Branches

- `master` — current stable release. Bugfixes and small enhancements land here when a public release is planned shortly.
- `develop` — exists only when a larger change is in progress against a future module version line. Not all modules have it; when it does exist, it can lag behind master, so rebase or pull before committing.

When in doubt, target `master`.

## Versioning

Modules follow [semantic versioning](https://semver.org/) (`MAJOR.MINOR.PATCH`), independent of HumHub core's own versioning.

| Change                                           | Version bump |
|--------------------------------------------------|--------------|
| Bug fix, minor enhancement                       | PATCH        |
| New feature, bump of `humhub.minVersion`         | MINOR or MAJOR |
| Breaking change to the module's public API       | MAJOR        |

The `version` in `module.json` is *only* bumped when a release is actually cut — unreleased work lives under the `(Unreleased)` section of `docs/CHANGELOG.md` with the same version still set.

## Raising the required HumHub core version

If your change relies on a feature only available in a newer HumHub core, bump `humhub.minVersion` in `module.json` and bump at least the MINOR version of the module. A PATCH bump is not enough.

If the required core version is not yet released, the change must go onto a `develop` branch — the module repo gets a `develop` branch (creating one if missing) and work targets it.

Note the required core version in `docs/CHANGELOG.md` under the upcoming version.

## Marketplace release

1. (If `develop` exists) merge `develop` into `master`.
2. Replace `(Unreleased)` in `docs/CHANGELOG.md` with today's date — match the format used by the previous entry.
3. Verify `version` in `module.json` matches the topmost CHANGELOG entry.
4. Commit `Release <version>` and push.
5. Create a GitHub release with tag `v<version>` and title `<version>` (no `v`). Body = the CHANGELOG section.

The marketplace upload runs automatically via the module's GitHub Actions workflow on the `release` event.

## Translations

For modules whose translations are managed via [translate.humhub.org](https://translate.humhub.org):

- **Do not** run `yii message/extract-module` to regenerate message files — the translation platform owns them.
- All `messages/*` changes must come from translate.humhub.org. Manual edits get overwritten on the next sync.
