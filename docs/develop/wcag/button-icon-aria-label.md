# Icon-only buttons: accessible name via tooltip

A button that shows only an icon — no visible text label — has no accessible name unless one is provided explicitly. Screen readers will either skip it or announce something useless like "button".

**Rule: every icon-only button must have a tooltip.**

The tooltip text is the semantic description of what the button does, making it the natural accessible name. The `Button` widget enforces this:

1. **Auto-derives `aria-label`** from the tooltip text (`data-bs-title`) when no explicit `aria-label` is set.
2. **Logs a `Yii::warning()`** in `YII_DEBUG` mode when an icon-only button is rendered with neither a tooltip nor an explicit `aria-label`.

## Correct usage

```php
use humhub\widgets\bootstrap\Button;

echo Button::danger()
    ->icon('delete')
    ->tooltip(Yii::t('base', 'Delete'))
    ->action('delete');
```

Rendered HTML (abbreviated):

```html
<button class="btn btn-danger btn-icon-only"
        data-bs-title="Delete"
        aria-label="Delete"
        data-action-click="delete">
    <i class="fa fa-trash"></i>
</button>
```

## Override aria-label explicitly

When the tooltip and the accessible name need to differ, set `aria-label` directly — it always takes precedence:

```php
echo Button::danger()
    ->icon('delete')
    ->tooltip(Yii::t('mymodule', 'Remove entry'))
    ->options(['aria-label' => Yii::t('mymodule', 'Remove this calendar entry')])
    ->action('delete');
```

## What to avoid

```php
// Missing tooltip — logs a warning in YII_DEBUG, no accessible name for screen readers
echo Button::primary()
    ->icon('settings')
    ->action('openSettings');
```

If you see the following in your application log, a button is missing its tooltip:

```
[warning][accessibility] Icon-only button rendered without tooltip/aria-label (icon: settings)
```

Fix it by adding `->tooltip(Yii::t(..., 'Settings'))`.

## Buttons with a visible label

Buttons that already have a text label do not need a tooltip for accessibility purposes. Adding one for extra context (e.g. a keyboard shortcut hint) is fine but optional.

## References

- `humhub\widgets\bootstrap\Button` — widget reference
- [Bootstrap 5 tooltips](https://getbootstrap.com/docs/5.3/components/tooltips/)
- [WCAG 2.1 — 4.1.2 Name, Role, Value](https://www.w3.org/TR/WCAG21/#name-role-value)
