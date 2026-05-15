# Coding Standards

HumHub follows [PSR-12](https://www.php-fig.org/psr/psr-12/) (extending [PSR-1](https://www.php-fig.org/psr/psr-1/)), with a few additional rules borrowed from the [Yii core code style](https://github.com/yiisoft/yii2/blob/master/docs/internals/core-code-style.md).

The configuration is checked into the repository:

- [`.php-cs-fixer.php`](../../.php-cs-fixer.php) — PHP-CS-Fixer ruleset (extends `@PER-CS`)
- [`rector.php`](../../rector.php) — Rector rules used for refactoring sweeps
- [`.editorconfig`](../../.editorconfig) — indentation, line endings, trailing whitespace

Run the formatter before submitting a pull request:

```sh
vendor/bin/php-cs-fixer fix
```

CI runs the same fixer in `--dry-run` mode and rejects PRs that diverge from the ruleset.

## JavaScript

JavaScript code under `static/js/` follows the existing patterns — 4-space indent, no semicolons omitted, `humhub.module(...)` registration. There is no automated formatter for JS at the moment; match the surrounding file.

## Commit style

- Imperative subject ("Fix X" not "Fixed X" or "Fixes X")
- Subject line under ~70 characters
- Body explains *why* when it isn't obvious from the diff
