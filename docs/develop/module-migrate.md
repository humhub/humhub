# Module Migration Guide

Migration notes for keeping a module compatible with newer HumHub core releases.

Each release line has its own file holding the breaking changes, new APIs and deprecations of
that cycle. A pull request that introduces a breaking change adds its entry to the file of the
release line it targets — never to this page, which only links the files.

## Release lines

- [Version 1.20](module-migrate-1.20.md) — in development on `next`
- [Version 1.19](module-migrate-1.19.md) — in development on `develop`
- [Version 1.18](module-migrate-1.18.md) — captcha framework, Codeception 5, mailer config keys
  - [Bootstrap 5 migration](module-migrate-1.18-bs5.md) — the theme and markup changes of 1.18
- [Version 1.17](module-migrate-1.17.md) — Manage-All-Content permission, CSS variables
- [Version 1.16](module-migrate-1.16.md) — search refactor, PHP 8.0 minimum
- [Version 1.15](module-migrate-1.15.md) — JS nonces, type restrictions, GUID validation
- [Legacy versions (1.14 and earlier)](module-migrate-legacy.md)
