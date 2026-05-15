# HumHub Developer Documentation

Reference docs for working on the HumHub core and writing modules. The full user-facing site lives at [docs.humhub.org](https://docs.humhub.org); this directory contains the developer subset, versioned with the source.

HumHub is built on the [Yii 2.0 PHP Framework](https://www.yiiframework.com/doc/guide/2.0/en/). Familiarity with Yii is assumed throughout — the docs here cover what HumHub adds on top.

## Getting started

- [Overview](intro-overview.md) — architecture, core modules, application structure
- [Development environment](intro-environment.md) — local setup, debug mode, queues
- [Coding standards](intro-coding-standards.md)
- [Build system](intro-build.md) — assets, grunt, releases
- [Pull requests](intro-pull-requests.md) — contribution workflow
- [Testing](intro-testing.md) — Codeception, unit/functional/acceptance

## Building modules

Everything specific to writing a HumHub module — packaging, lifecycle, persistence.

- [Module development](module-development.md) — the main starting point
- [Module base class](module-base-class.md) — `Module` and `ContentContainerModule`
- [Module lifecycle](module-lifecycle.md) — install, enable, disable, uninstall
- [Module content](module-content.md) — content containers
- [Module event handler](module-event-handler.md) — catalogue of catchable core events
- [Module migration guide](module-migrate.md) — upgrading modules between core versions
- [Module change behavior](module-change-behavior.md) — overriding widgets, menus, controllers
- [Module Git workflow](module-git.md)

## Platform concepts

The systems modules plug into. Read the ones relevant to what your module does.

- [Models & migrations](concept-models.md)
- [Users](concept-users.md) — user, profile, groups
- [Content](concept-content.md) — `ContentActiveRecord`, content containers, visibility
- [Stream](concept-stream.md) — stream entries, filters, suppliers
- [Events](concept-events.md) — registering and firing events
- [Module settings](concept-settings.md) — per-module and per-container settings
- [Permissions](concept-permissions.md) — permission model, guest access
- [Notifications](concept-notifications.md)
- [Activities](concept-activities.md)
- [Files](concept-files.md) — uploads, attachments
- [Live updates](concept-live.md)
- [Search](concept-search.md)
- [Internationalization](concept-i18n.md)

## UI & frontend

Widgets, menus, theming, and the HumHub JavaScript layer.

- [Widgets](ui-widgets.md)
- [Menus](ui-menus.md)
- [Snippets](ui-snippets.md) — sidebar snippets
- [JavaScript overview](ui-js-overview.md) — the `humhub.module` system
- [JavaScript actions](ui-js-actions.md) — `data-action-*` handlers
- [JavaScript components](ui-js-components.md) — `Component` and `Widget`
- [JavaScript UI additions](ui-js-uiadditions.md)
- [JavaScript modals](ui-js-modals.md)
- [JavaScript stream](ui-js-stream.md)
- [JavaScript client](ui-js-client.md) — REST/AJAX wrapper

## User & authentication (HumHub 1.19+)

How user accounts and login are wired together.

- [Authentication](user-auth.md) — AuthClient families, login flow
- [UserSource](user-source.md) — provisioning model, attribute sync, LDAP/SCIM

## Advanced topics

- [Console commands](advanced-console.md)
- [Security](advanced-security.md)
- [OEmbed providers](advanced-oembed.md)
