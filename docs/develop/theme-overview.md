# Theming Overview

Most appearance changes don't need a theme. *Administration → Settings → Appearance* covers:

- Logo, favicon, login background, mail header image
- Eight theme colours (primary, accent, secondary, success, danger, warning, info, light)
- Custom SCSS — arbitrary stylesheet snippets compiled into the active theme

A dedicated theme module makes sense when you need:

- To override view templates (HTML structure of a page, a widget, or a mail layout)
- To ship multiple coordinated changes as a single package — e.g. for an Enterprise rollout or the marketplace
- To extend the default SCSS with more than a few rules (mixins, variable overrides, partials)

If your change fits in the Appearance UI, stay there — themes are a heavier tool with an upgrade cost.

## Lifecycle

A theme is a folder under `themes/` (or shipped via a theme module). It is activated under *Administration → Settings → Appearance → Theme*. Multiple themes can coexist; one is active at a time.

A theme can declare a **parent theme** (via the `$baseTheme` SCSS variable) and inherit unmodified views and styles from it. This is the recommended pattern — derive from `HumHub` (Community Edition) or `enterprise` (Enterprise Edition) and override only what you need.

## What's in a theme

- [Structure](theme-structure.md) — folder layout, SCSS files, variables, parent themes
- [View and mail overrides](theme-views.md) — how view files are looked up and how to override them

## Distribution

A theme can be shipped as a regular HumHub [module](module-development.md) so it installs and updates via the marketplace. Set the module ID with a `theme-` prefix (e.g. `theme-example`), put the theme directory under `themes/<name>/` inside the module, and configure the module class normally. See the [marketplace module rules](https://www.humhub.com/marketplace) before submitting.
