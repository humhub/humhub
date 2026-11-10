# Release Packages

HumHub release packages (`humhub-<version>.zip`, `humhub-<version>.tar.gz`) are built by the
GitHub workflow `.github/workflows/release-package.yml`.

## When packages are built

- Automatically when a GitHub release is published (`release: published`).
- Manually via *Actions → Release Package → Run workflow* with a tag (e.g. `v1.19.2`).
  The build tools are taken from the selected branch, the sources from the tag, so older tags can be rebuilt.

The packages are kept as workflow artifact (14 days) and uploaded to the humhub.com web server.

## How a package is built

`.github/release/build-package.sh`:

1. PHP version = `minSupportedPhpVersion` from `protected/humhub/config/common.php` of the tag
   (the workflow fails if `config.platform.php` in `composer.json` is set and differs).
2. `composer install` (incl. dev), `npm ci`, `grunt build-assets`.
3. `composer install --no-dev` — packages contain production dependencies only.
4. Copy everything except the entries of `.github/release/exclude.txt` to `humhub-<version>/` and pack it.
5. Write `humhub-<version>.json` (version, tag, commit, PHP version, build date, size and SHA256 of both archives).

## Files outside `protected/humhub`

Everything in the repository ends up in the package unless it is listed in `.github/release/exclude.txt`.

**When a PR adds, moves or removes files or directories outside `protected/humhub`, check whether
`.github/release/exclude.txt` has to be adjusted** — development-only files (tooling configs,
build scripts) must be excluded, files needed by an installation must not be.

**Also check the updater's package rules.** The updater module installs core updates from the same
full package and overwrites everything that is not covered by a rule. If a PR adds files outside
`protected/humhub` that users customise (and that must therefore not be overwritten), moves
directories, or removes files that should disappear from existing installations, the updater needs a new
rules section for that core version, released before the core release — see
[`config/package-rules.php`](https://github.com/humhub/updater/blob/master/config/package-rules.php) and
[`docs/PACKAGE-RULES.md`](https://github.com/humhub/updater/blob/master/docs/PACKAGE-RULES.md).
