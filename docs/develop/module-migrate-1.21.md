# Module Migration — Version 1.21

Breaking changes, new APIs and deprecations of the 1.21 release cycle.

- **Removed: the colour tokens and Sass variables deprecated in 1.20 and 1.18.** See
  [Colours](../ui/colours.md#replaced-tokens-and-variables) for the replacements.
  - The CSS variables `--hh-text-color-*`, `--hh-background-color-*`, `--hh-background3`,
    `--hh-background4`, `--hh-{background,text,border}-color-{success,warning,danger}`,
    `--hh-text-color-contrast` and those of 1.18 (`--text-color-*`, `--background-*`, `--primary`,
    `--accent`, `--info`, `--success`, `--warning`, `--danger`, `--link`, `--default`). A rule still
    reading one gets no colour.
  - The former Sass variable names (`$text-color-main`, `$background-color-page`, `$background3`,
    `$text-color-{color}-contrast`, …): a theme that still sets one no longer changes anything, a
    theme `build.scss` reading one fails to compile. `variables-deprecated.scss` is removed.
  - `ThemeHelper::DEPRECATED_VARIABLES` is removed: `Theme::variable()` with a former name, e.g.
    `variable('text-color-soft2')` or `variable('text-color-contrast')`, returns `null` (or the
    given default). Use the new name (`body-tertiary-color`), or `ScssHelper::getColorContrast()`.
