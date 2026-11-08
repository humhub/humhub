# Appearance & Accessibility

Appearance becomes its own item in the Administration menu, and users get an account page
**Appearance & Accessibility**. Both bundle what today is spread over *Settings → General*,
*Settings → Appearance* and *Account → General*, and both carry the new colour mode and the
accessibility options.

Out of scope: custom theme colours for dark mode, a colour mode switch in the top bar, user choices for guests, appearance settings
per space, the mobile app.

## Administration

A new top-level entry in `AdminMenu`:

| id | Label | Icon | Route | sortOrder | Visible |
|---|---|---|---|---|---|
| `appearance` | Appearance | `paint-brush` | `/admin/appearance` | 550 (between Modules and Settings) | `ManageSettings` |

`admin/controllers/AppearanceController` with one action per tab. The tabs are the `PillTabs`
menu `admin/widgets/AppearanceMenu`, so modules can add a tab (e.g. a theme module).

| Tab | Action | Form | Settings |
|---|---|---|---|
| **General** | `index` | `AppearanceGeneralForm` | Name of the application, Default language, Default time zone, Date input format |
| **Logo & Images** | `images` | `AppearanceImagesForm` | Logo, Icon, Login background, E-mail header image |
| **Theme** | `theme` | `ThemeSettingsForm` | Theme, the nine theme colours with their "Default" switches |
| **Dark Mode** | `dark-mode` | `DarkModeSettingsForm` | see below |
| **Accessibility** | `accessibility` | `AccessibilitySettingsForm` | defaults of the user options, see below |
| **Custom SCSS** | `custom-scss` | `CustomScssForm` | Custom SCSS |

Saving the Theme or Custom SCSS tab rebuilds the theme CSS. Every tab saves with a full page load,
so a changed option applies at once.

### Dark Mode

The tab shows a notice that the dark mode is experimental; the user setting carries the badge *Experimental*.

| Setting | Key (`admin` module) | Values | Default |
|---|---|---|---|
| Allow dark mode | `colorMode.allowDark` | bool | off |
| Default colour mode | `colorMode` (`user` module, global) | `light`, `system` | `light` |
| Dark logo | file `logo_image_dark` (as the logo) | image, optional | – (the logo is used) |

The fields below *Allow dark mode* are disabled while it is off. The theme colours are the same in
both modes; the dark values of the derived tokens come from `concept-design-tokens.md`.

While *Allow dark mode* is off, every page renders `data-bs-theme="light"`, the user option is
hidden and stored user values are ignored (kept, not deleted).

### Accessibility

The defaults for guests and for users who never chose. They are the global values of the user
settings (`user` module), so `getInherit()` falls back to them:

| Setting | Key (`user` module) | Values | Default |
|---|---|---|---|
| High contrast | `a11y.contrast` | `system`, `off`, `on` | `system` |
| Reduced motion | `a11y.motion` | `system`, `off`, `on` | `system` |
| Readable font | `a11y.font` | `off`, `on` | `off` |

### Settings → General

The *Appearance* tab is removed from `SettingsMenu`. *Settings → General* keeps Base URL, tour,
dashboard, friendship and maintenance mode, and takes over the non-visual fields of the old
Appearance page: pagination size, user display name and subtitle, "My Spaces" sorting, default
stream order, post title mode.

`DesignSettingsForm` is removed; its fields go to the forms above and `BasicSettingsForm`. Existing
setting keys stay, so there is no data migration. `/admin/setting/design` redirects to
`/admin/appearance/theme`. Both removals go into `module-migrate-1.20.md`.

## Account

A new entry in `AccountMenu`:

| Label | Route | sortOrder |
|---|---|---|
| Appearance & Accessibility | `/user/appearance` | 111 (between General and Security & Privacy) |

`user/controllers/AppearanceController`, tabs in the `PillTabs` menu
`user/widgets/AppearanceSettingsMenu`:

**Appearance** (`index`, `AppearanceSettings`)

| Setting | Storage | Values |
|---|---|---|
| Colour mode <sup>Experimental</sup> | user setting `colorMode` (`user` module) | System, Light, Dark; only shown when the admin allows dark mode |
| Language | `user.language` (moved from General) | allowed languages |
| Time zone | `user.time_zone` (moved from General) | time zones |
| Editor | user setting `markdownEditorMode` (moved from General) | Rich Text, Markdown |

**Accessibility** (`accessibility`, `AccessibilitySettings`)

| Setting | Storage | Values |
|---|---|---|
| High contrast | user setting `a11y.contrast` (`user` module) | Use system setting, Off, On |
| Reduce motion | user setting `a11y.motion` (`user` module) | Use system setting, Off, On |
| Readable font | user setting `a11y.font` (`user` module) | switch |

Each control shows the instance default ("Default: Use system setting"). A user value equal to the
default is deleted, so a changed admin default reaches every user who never chose. Reads use
`settings->contentContainer($user)->getInherit()`.

*Account → General* keeps Profile tags and the introduction tour.

## Rendering

A component `humhub\components\Appearance` (`Yii::$app->appearance`) resolves the
effective value of each option for the current user (user setting → instance default). An inline
script in `<head>` (with nonce, registered by `View::endBody()`, so every layout and theme gets
it) sets them as attributes of `<html>` before the first paint:

| Option | Attribute | Values | "system" |
|---|---|---|---|
| Colour mode | `data-bs-theme` | `light`, `dark` | the script sets it from `prefers-color-scheme` and follows its change |
| High contrast | `data-hh-contrast` | `more`, `off` | no attribute; CSS applies the same rules under `@media (prefers-contrast: more)` on `:root:not([data-hh-contrast])` |
| Reduced motion | `data-hh-motion` | `reduce`, `off` | as above with `prefers-reduced-motion: reduce` |
| Font | `data-hh-font` | `readable` | – |

The SCSS mixins `hh-high-contrast` and `hh-reduced-motion` (`_mixins.scss`) write both selectors.
JS reads the effective values through the new JS module `humhub.modules.ui.appearance`
(`colorMode()`, `reducedMotion()`, `highContrast()`); while motion is reduced, `$.fx.off` turns the
jQuery animations off. The module also keeps `data-hh-input-modality` on `<html>` (`keyboard` once
Tab moves the focus, `pointer` after a click; `keyboardModality()`), which gates the focus ring of a
text field (`_form.scss`).

**Dark logo** (`Yii::$app->img->logoDark`): `SiteLogo` renders both images when a dark logo exists
and the dark mode is allowed, `.hh-logo-light` and `.hh-logo-dark`, and CSS shows the one of the
active mode. E-mails always use the light logo.

**High contrast** redefines semantic tokens only (`concept-design-tokens.md`):
`--bs-secondary-color` and `--bs-tertiary-color` = `--bs-body-color`, `--bs-border-color` and
`--hh-border-color-strong` = `$hh-border-color-contrast` (`#767676`, dark `#8a8a8a`, 4.5:1). Links in
running text (`a:not([class])`, Markdown, richtext) are underlined, the focus ring is a 3px outline in
`--bs-emphasis-color`. It works in both colour modes.

**Reduced motion** sets the animation and transition durations to `0.01ms` (so `animationend` and
`transitionend` still fire) and `scroll-behavior: auto` for all elements (`_accessibility.scss`); the
`prefers-reduced-motion` blocks of the partials are replaced by this one rule, and the Vue components
read `data-hh-motion` before the media query.

**Readable font**: Lexend (SIL OFL), designed for reading fluency, also with dyslexia. Variable
woff2 from `npm-asset/fontsource-variable--lexend` (Composer, as Open Sans), registered by
`ReadableFontAsset` only while the option is on. It sets `--bs-body-font-family`; code blocks keep
their monospace font.
