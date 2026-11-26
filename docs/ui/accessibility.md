# Accessibility

> The entry point to accessibility in the styleguide: what HumHub aims for, what users can set,
> and the rules every component follows. The details are on the pages linked here and in the
> *Accessibility* section of each component page.
> · For developers: [Accessibility](../develop/accessibility.md) (building and checking)

HumHub aims for **WCAG 2.2, level AA**. In the EU this is what the harmonised standard
EN 301 549 asks of web content (from its version 4.1.1; the BITV 2.0 and the European
Accessibility Act refer to it). Accessibility is part of every component, not an option
([Principles](principles.md), 28).

## What users can set

*Account → Appearance & Accessibility*. The administrator sets the defaults for guests and for
users who never chose in *Administration → Appearance*; *System* follows the operating system.

| Option | What it does | Default |
|---|---|---|
| **Colour mode** | Light, dark or as the system; dark only when the administrator allows it | Light |
| **High contrast** | Stronger text and border colours (7:1), underlined links, a 3px focus ring | System |
| **Reduce motion** | No animations and transitions | System |
| **Readable font** | Lexend, a font designed for easier reading, also with dyslexia | Off |
| **Keep messages open** | A [Toast](toast.md) stays until it is closed (WCAG 2.2.1) | Off |

Only for the administrator: **Alternative text for images** (*off*, *optional*, *required*):
whether the editor asks for a description of an uploaded image.

A component therefore works in each combination of colour mode and high contrast, with reduced
motion, and also with what the browser sets: zoom up to 400 %, a larger font size, and the forced
colours of a Windows contrast theme.

## The rules in short

**Colour and contrast** ([Colours](colours.md))
- Text reaches 4.5:1 on its surface, in high contrast 7:1. Lines, icons and the marks of a state
  (checked, active, selected) reach 3:1 against what is next to them.
- Text that has to be read is the body or the emphasis colour, never a lighter grey.
- Colour is never the only signal: a dot or a coloured state also has text, visible or hidden
  ([Principles](principles.md), 14). An active entry is also bold, filled or marked otherwise.
- In forced colours the browser drops fills and shadows. A state shown only by them gets a rule
  of its own in system colours.

**Text and layout** ([Typography](typography.md))
- Sizes in `rem`, so the text follows the browser's font size. Nothing is cut off at 200 % zoom
  or with more letter and line spacing.
- At 320px width (400 % zoom) the page reflows into one column: no sideways scrolling, except a
  table, which scrolls in its own box.

**Keyboard and focus** ([Keyboard](keyboard.md))
- Tab reaches every control, in the visual order. What hover shows, focus shows. Escape closes
  the innermost popup and returns the focus to its trigger.
- The focus ring is always visible, and nothing fixed (top bar, toast) covers the focused
  element entirely.

**Names and structure**
- Every control has a name: its visible text, or for an icon only a tooltip that becomes its name
  ([Buttons](buttons.md)). The name contains the visible text, so speech input finds it
  (WCAG 2.5.3).
- Every field has a label, also in a filter bar ([Form Fields](form-fields.md)). Errors are shown
  at the field and said to screen readers.
- One `h1` per page, headings in order; landmarks for header, navigation, main content and
  sidebar.
- Native elements first: `<button>` for an action, `<a href>` for a navigation. ARIA only where
  HTML has no element for the job.
- A change the page shows by itself (a new comment, a like) is also told to screen readers
  ([Keyboard](keyboard.md#announcing)). An icon that only decorates is hidden from them.

**Pointer**
- A target is at least 24 × 24px, or has that much room around it (WCAG 2.5.8).
- What can be dragged can also be done without dragging: a button, a menu entry (WCAG 2.5.7).

**Time and motion**
- Nothing that matters disappears by itself before it can be read; with *Keep messages open* not
  at all.
- Animations are short and off with *Reduce motion*.

## Checking

Each change of a component is checked with the keyboard ([Keyboard](keyboard.md#checking)), in
the dark mode and in high contrast. Pages of the core are also checked automatically on every pull
request; how, and how to check a module: [Accessibility](../develop/accessibility.md#checking).
