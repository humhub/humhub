# Pull Requests

## Issue reference

New features and bug fixes should reference an issue — a single place to discuss scope and decisions. Before starting, check the [issue list](https://github.com/humhub/humhub/issues) for an existing entry; if you find one, leave a comment that you're picking it up. Otherwise open a new issue (or, for a straightforward fix, just open the PR directly).

Small documentation fixes don't need an issue — the PR ID is enough. The changelog entry then references the PR number.

## Branches

**Core repository ([github.com/humhub/humhub](https://github.com/humhub/humhub)):**

- `master` — bug fixes and critical translation updates against the current stable release
- `develop` — new features, refactoring, non-critical fixes for the next release

Branch naming: `enh/<issue-id>-<short-info>` or `fix/<issue-id>-<short-info>`.

**Module repositories ([github.com/humhub](https://github.com/humhub), [github.com/humhub-contrib](https://github.com/humhub-contrib)):**

Most modules have a single `master` (or `main`) branch. If a `develop` branch exists and your PR is a major change, target `develop`.

## Changelog

Add a line to the changelog of the version under development:

```
Enh #999: short description (Your Name)
Fix #999: short description (Your Name)
```

`#999` is the GitHub issue number; if there is no issue, use the PR number.

Changelog locations:

| Repo      | Path                |
|-----------|---------------------|
| Core      | `CHANGELOG.md`      |
| Modules   | `docs/CHANGELOG.md` |

For modules without an existing "Unreleased" section, create one and bump `module.json` accordingly:

- Bugfix / minor enhancement → patch version (`1.0.0` → `1.0.1`)
- New feature, or bump of `humhub.minVersion` → minor or major version (`1.0.0` → `1.1.0`)

## Tests

Unit tests are welcome and make review much easier. See the [testing guide](intro-testing.md) for the layout.

## Contributor License Agreement

To have your contribution accepted you must accept the HumHub CLA, signed once via [cla-assistant.io/humhub/humhub](https://cla-assistant.io/humhub/humhub). The CLA bot comments on your first PR with the signing link.

## Documentation

- **Core changes** that affect modules go into [`MIGRATE-DEV.md`](https://github.com/humhub/humhub/blob/develop/MIGRATE-DEV.md).
- **Module features** should be reflected in the module's `README.md` and `docs/`.
