# HumHub Core — Claude Guide

## Project Overview

HumHub is an open-source social network platform built on **Yii2 (PHP 8.2+)**. This repository (`humhub/humhub`) is the core framework. Functionality is extended through modules — built-in (core modules) and external (community/official modules).

## Repository Structure

```
/protected/humhub/
├── components/         # Base classes (Module.php, ActiveRecord, etc.)
├── modules/            # Core modules (always enabled, part of this repo)
│   └── <module>/
│       ├── config.php  # Event registrations — primary impact point for core changes
│       ├── module.json # Module metadata (no humhub version fields for core modules)
│       ├── Module.php  # Module class
│       ├── Events.php  # Event handler methods
│       └── tests/      # Codeception test suite
└── tests/              # Core test suite (Codeception)
```

External modules live in separate repositories (e.g., `humhub/calendar`, `humhub-contrib/onlyoffice`).

## Identifying a Module Repository

A GitHub repository is a HumHub module **if and only if** it has a `module.json` in its root directory. Both `humhub/*` and `humhub-contrib/*` orgs contain non-module repos — always check for `module.json` before treating a repo as a module.

## module.json (External Modules)

```json
{
    "id": "calendar",
    "name": "Calendar",
    "version": "1.8.10",
    "humhub": {
        "minVersion": "1.19",
        "maxVersion": "1.20"
    }
}
```

- `humhub.minVersion`: minimum required core version. Update when module depends on new core APIs.
- `humhub.maxVersion`: only set when the module breaks with a newer core version. When this exists, there is usually a `develop` branch with the compatibility fix.
- Core modules (in this repo) have no `humhub` version fields in their `module.json`.

## Event System

Every module registers event listeners in `config.php` as `[class, event, callback]` tuples:

```php
'events' => [
    ['class' => Menu::class, 'event' => Menu::EVENT_INIT, 'callback' => [Events::class, 'onMenuInit']],
]
```

Handler methods are always in `Events.php`. This is a fixed pattern across all modules.

**This is the primary breaking point for core changes.** Renaming, moving, or removing a core class referenced in any module's `config.php` silently breaks that module at runtime.

## Migration guide

`docs/develop/module-migrate-<version>.md` is the authoritative record of all breaking API changes. Always read the file of the relevant release line first when assessing module impact.

Structure:
- One file per release line: `module-migrate-1.20.md` (`next`), `module-migrate-1.19.md` (`develop`), `module-migrate-1.18.md`, etc.
- `docs/develop/module-migrate.md` is **only an index** linking those files — never add entries there. Entries go into the file of the release line the PR targets, which keeps `develop` → `next` merges conflict-free.
- Pre-1.15: `docs/develop/module-migrate-legacy.md`
- Sections within each version: Replaced classes · Removed classes · Replaced methods · Deprecated · Refactored

When doing impact analysis:
1. Read the migration file of the targeted release line to identify what changed in this PR/branch
2. Search module `config.php` files for references to changed classes (event breakage)
3. Search module PHP files for direct class/method usage
4. Determine if `humhub.minVersion` in `module.json` needs updating

## Branching Strategy

| Branch | Purpose |
|--------|---------|
| `master` | Stable release |
| `develop` | Next version — primary development branch |
| `next` | Version after next |

**Target branch for PRs:** new features and enhancements go into `develop`, never directly into `master`. Only bugfixes for the current stable release are committed to `master`. Base every PR on the branch that matches the type of change.

External modules follow the same convention. A module's `develop` branch targets the upcoming core version. Verify by checking `humhub.minVersion` in the module's `module.json` on that branch.

### `@since` annotations

New features belong to the release line of the branch they are developed on, not to the previous version: `@since 1.20` on `next`, `@since 1.19` on `develop`. Use it in docblocks even when surrounding code still shows older `@since` values from past PRs. `protected/humhub/config/common.php` `.version` tells you the current cycle of the checked-out branch.

## Changelog

Every PR must include a changelog entry. In this core repo the changelog is `CHANGELOG.md` at the repository **root** (external modules use `docs/CHANGELOG.md`). Add a bullet under the `X.Y.Z (Unreleased)` section of the branch's own release line (open one at the top if the branch has no section of its own yet — `next` must not add entries to the `develop` section) in the form `- <Tag> #<PR>: <description>`, where `<Tag>` is `Enh`, `Fix`, etc. — match the existing entries. Do not bump the version for unreleased changes; the version is only bumped when a release is cut.

## Running Tests

### Core Tests

```bash
# Start test server first (required)
grunt test-server

# Run all core tests
grunt test

# Run tests for a specific core module
grunt test --module=content

# Run specific suite (unit, functional, acceptance)
grunt test --suite=unit
grunt test --suite=functional

# Run with build step
grunt test --build
```

### External Module Tests

Set up environment variables (locally, e.g. via `~/.env`):

```bash
export HUMHUB_PATH="/path/to/core/"
export HUMHUB_VENDOR_BIN="$HUMHUB_PATH/protected/vendor/bin"
export HUMHUB_TEST_YII="$HUMHUB_PATH/protected/humhub/tests/codeception/bin/yii"
```

Run module tests:

```bash
cd <module>/tests
php $HUMHUB_VENDOR_BIN/codecept run

# Specific suite
php $HUMHUB_VENDOR_BIN/codecept run unit
php $HUMHUB_VENDOR_BIN/codecept run functional
```

Test suites per module: `unit`, `functional`, `acceptance`, `api` (not all modules have all suites).

### CI Checkout Structure (GitHub Actions)

All module CI workflows (from `humhub/module-coding-standards`) check out core first, then the module into `protected/modules/<module-id>/`:

```yaml
- uses: actions/checkout@v4          # Core at workspace root
  with:
    repository: humhub/humhub
    ref: develop

- uses: actions/checkout@v4          # Module inside core
  with:
    path: protected/modules/<module-id>
```

In CI, `HUMHUB_PATH=$GITHUB_WORKSPACE`. Tests run as:

```bash
cd $GITHUB_WORKSPACE/protected/modules/<module-id>/tests
php $GITHUB_WORKSPACE/protected/vendor/bin/codecept run --env github
```

## Cross-Module Impact Analysis Workflow

When a PR changes public core APIs:

1. Read the `Unreleased` section of `docs/develop/module-migrate.md` to understand what changed
2. Search `config.php` across all module repos for old class/event names
3. Search PHP files for direct usage of changed classes or methods
4. For each affected module: check if `develop` branch exists and what its `humhub.minVersion` is
5. Determine needed changes: event references, class imports, method calls, `module.json` version bump

Search scope: `humhub/*` and `humhub-contrib/*` orgs on GitHub. Use `module.json` presence in root to confirm a repo is a module.

## Private Module Repositories

Some module repos are private. Their contents must never be exposed to non-team members. Private repos do not appear in `gh search code` results without explicit token access, so impact analysis via GitHub search is safe by default.

## Module Coding Standards

`humhub/module-coding-standards` is a Composer dev-dependency in all modules. It provides:
- Shared Rector, PHP CS Fixer, and PHPStan configs
- Reusable GitHub Actions workflows (in `.github/workflows/`) — called via `workflow_call` from module repos
- Workflow templates (in `workflows/`) — manually copied by modules into their `.github/workflows/`
- `CLAUDE.md` template — copied to module root to give Claude context about module conventions

When adding new shared CI behaviour (e.g. Claude workflows), add the reusable workflow to `module-coding-standards/.github/workflows/` and the call-template to `module-coding-standards/workflows/`.

## Coding Conventions

- PHP 8.2+ strict types
- Yii2 framework patterns throughout
- Rector for automated code quality (`rector.php` in root)
- No inline event handler logic — always delegate to `Events.php`
- Module settings: `$module->settings->get('key')` / `$module->settings->set('key', $value)`
- Resources (JS, CSS, images): `resources/` directory, served via Yii2 AssetBundles
