# Accessibility

How core and modules meet **WCAG 2.2, level AA**: the tools core provides, and how a change is
checked. What the rules are, and what users can set, is in the styleguide:
[Accessibility](../ui/accessibility.md), with [Principles](../ui/principles.md),
[Keyboard](../ui/keyboard.md) and [Colours](../ui/colours.md).

## Building

Use the core's widgets, components and tokens. They carry names, roles, focus handling and the
colours of all modes; a copy of their markup or a hard-coded colour loses that.

### Names

**Icon-only buttons** take their accessible name from their tooltip. The `Button` widget
(`humhub\widgets\bootstrap\Button`) sets `aria-label` from the tooltip unless one is given, and
with `YII_DEBUG` logs a warning (category `accessibility`) for an icon-only button that has
neither:

```php
use humhub\widgets\bootstrap\Button;

echo Button::danger()
    ->icon('delete')
    ->tooltip(Yii::t('base', 'Delete'))
    ->action('delete');
// <button class="btn btn-danger btn-icon-only" data-bs-title="Delete" aria-label="Delete" …>
```

An explicit `aria-label` (`->options(['aria-label' => …])`) wins, e.g. when the name has to say
more than the tooltip. It has to contain the tooltip's text: what a control shows is part of its
name (WCAG 2.5.3, speech input).

**Icons** (`Icon::get()`) are decorative and hidden from screen readers (`aria-hidden`), unless:

- `->tooltip($text)`: the text is also in a visually hidden span; with `->focusable()` the icon is a
  tab stop of its own, so keyboard users get the tooltip too.
- the caller names the icon with `aria-label` (`htmlOptions`), for an icon that is a control itself
  (e.g. a drag handle with `role="button"`): then it stays perceivable.

**Fields** get their label from `ActiveField`. A label that would only repeat the context (a
search field, a filter) is visually hidden (`visually-hidden`), never left out; a `title` or a
placeholder is not a name. **Table headers** of `GridView` get `scope="col"`; a column without a
visible header (an image, the actions) gets a visually hidden one, as `user\grid\ImageColumn`
does.

**Visually hidden text** (`.visually-hidden`) names what is shown only by a symbol: a dot, a
count, a state. It takes the contrast colour of what it sits on, because checkers read its colour
too (`.badge-dot`).

### Announcements and focus

- `humhub.ui.status.announce(text)` says a change the page shows by itself (a new comment, the
  count of a like) to screen readers without a toast. Toasts (`module.success()`, …) are announced
  anyway. See [Keyboard](../ui/keyboard.md#announcing).
- The focus helper `ui.focus` hands the focus on after a deletion, a load or a dialog
  ([Keyboard](../ui/keyboard.md#moving-the-focus)).
- The focus ring is the mixin `hh-focus-ring` (`hh-focus-ring(true)` inside the edge); a
  component only adds it where Bootstrap turns its own off ([Colours](../ui/colours.md#lines-and-focus)).

### Colour modes and options

- Colours are tokens (`var(--bs-body-color)`, `var(--hh-accent-fg)`, …), never literal values:
  they follow the dark mode and high contrast ([Colours](../ui/colours.md)). Stylelint
  (`npm run lint:scss`) rejects a few common mistakes, e.g. a theme colour as a text colour.
- The user's options are attributes of `<html>`: `data-bs-theme`, `data-hh-contrast`,
  `data-hh-motion`, `data-hh-font` ([concept](concept-appearance.md#rendering)). In SCSS, the
  mixins `hh-high-contrast` and `hh-reduced-motion` (`_mixins.scss`) cover the option and the
  system setting at once; in JS, `humhub.modules.ui.appearance` (`colorMode()`,
  `highContrast()`, `reducedMotion()`).
- Forced colours (a Windows contrast theme) need `@media (forced-colors: active)` where a state
  is only a fill or a shadow: system colours (`Highlight`, `HighlightText`, `CanvasText`), see
  [Colours](../ui/colours.md).

## Checking

### Automated

**The `a11y` suite** checks a fixed set of core pages with axe-core against WCAG 2.2 A/AA, in the
light and the dark mode, with and without high contrast. The workflow `.github/workflows/a11y.yml`
runs it on every pull request that changes the core and puts the new violations into the job
summary; for now it only reports, later a new violation fails the job. Known violations, each with
its planned fix, are listed in `protected/humhub/tests/codeception/a11y/known.php`; a fix removes
its entry. Running it locally, the options and the list: [Testing](intro-testing.md#accessibility-checks).

**A module** checks its pages by calling the core's workflow with its ID and the paths of its
pages; the module is checked out into the core and enabled:

```yaml
# .github/workflows/a11y.yml of the module
name: Accessibility Checks
on:
    pull_request:
jobs:
    check:
        uses: humhub/humhub/.github/workflows/a11y.yml@develop
        with:
            module: calendar
            pages: /calendar/global/index
```

**While developing**, the internal module *Accessibility Audit* (`humhub/a11yAudit`, axe-core)
shows the violations of the page in the browser; the browser extensions of axe and WAVE do the
same.

### Manual

Automated checks find only part of the problems. Before a component is done:

- **Keyboard**: the walk in [Keyboard](../ui/keyboard.md#checking).
- **Modes**: light and dark, each with high contrast; forced colours (Chrome DevTools → Rendering →
  *Emulate CSS media feature forced-colors*).
- **Zoom and text**: 200 % and 400 % zoom (320px wide), the browser's font size at its largest.
- **Screen reader**: NVDA with Firefox or VoiceOver with Safari, for what announces itself
  (dialogs, toasts, loaded content).
