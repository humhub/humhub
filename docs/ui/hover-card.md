# Hover Card

> Found in the design: avatars and names in the stream, comments, tasks, calendar, tables
> (person and space columns), activities
> · Core: not available. The contrib module `popover-vcard` ("Virtual Card Popover") offers it today.
> · Technology: **Both** (one shared card for PHP and Vue markup)

A small preview of a **person or space** that appears when the pointer rests on their avatar or
name. People can see who it is (picture, name, job title or description) without leaving the page,
and get to the profile from there.

![Tooltip and hover card](images/tooltip-hover-card.svg)

## Anatomy

- Cover (banner, or a generated pattern), picture, name, subtitle (job title, space description)
- Optional: a relationship button (Follow, Add friend, Join), see
  [Entity Card](entity-card.md#relationship-buttons)
- Cover, picture and name link to the profile or space

## Behaviour

- **Opens** when the pointer rests on an avatar or a linked name for about 350ms. Moving across
  avatars on the way elsewhere opens nothing.
- **Stays open** while the pointer moves toward the card and while it is on the card, so its links
  can be used.
- **Closes** when the pointer leaves, on scroll and on Escape.
- **Placement:** above the avatar first, otherwise right, left or below, always inside the window.
  It is never clipped by scrolling boards or tables.
- **Data** loads lazily on the first open and is cached for the page.
- **Click** on the avatar or name still goes to the profile. The hover card doesn't change what a
  click does.
- **Touch:** no hover card. A tap goes to the profile.
- While hover cards are active, the avatar's own tooltip is not shown.

## Accessibility

- The hover card is an **enhancement**. Everything in it is also reachable through the profile
  link.
- Keyboard users reach the profile via the link and don't get the card. A keyboard trigger could
  follow later.
- Reduced motion: no fade or slide.

## Markup and styles

The hover card is a Bootstrap **`.popover`**, opened by Bootstrap's Popover JS (Popper
placement). Class names and SCSS follow [Styling with Bootstrap 5](bootstrap.md).

| Class | Element |
|---|---|
| `.popover.hover-card` | The card (`customClass: 'hover-card'`) |
| `.popover-body` | Holds the parts |
| `.hover-card-cover`, `.hover-card-image`, `.hover-card-title`, `.hover-card-subtitle` | Cover, picture, name, subtitle |
| `.hover-card-actions` | The relationship button |

- **File:** `_hover-card.scss` (new), behind `$prev-hover-card`.
- **Sass variables:** Bootstrap's `$popover-max-width`, `$popover-border-radius`,
  `$popover-box-shadow`, `$popover-body-padding-*`; plus `$hover-card-width`,
  `$hover-card-cover-height`, `$hover-card-image-size`.

## Implementation in core

**Today:** not in core. The contrib module **`popover-vcard`** ("Virtual Card Popover") adds a
hover card:

- a layout addon (`VCardLoader`) and `humhub.vcard.popover.js`
- server-rendered cards for users and spaces (`VCardUser`, `VCardSpace`)
- admin-configurable content
- extension points for other modules (e.g. badges)

`UserImage.vue` and `SpaceImage.vue` already set `data-contentcontainer-id`, which can identify
the container.

**Proposal:**

- One shared hover card, bound by delegation to elements with `data-hover-card="user|space"` and
  the container id (PHP `Image` widgets and links, and the Vue `UserImage` / `SpaceImage`).
- Data from a small endpoint (`GET /api/v2/user/{guid}/card`, `…/space/{guid}/card`), cached per
  page. The relationship state comes from the existing state endpoints.
- A user setting (or a platform setting) to switch hover cards off.

## Open questions

1. **Core or module:** move the hover card into core (as proposed), or restyle `popover-vcard` to
   this design and keep it a module?
2. **Content:** picture, name and subtitle only, or also a relationship button and a short
   description?
3. **Keyboard access:** add a keyboard trigger (e.g. focus plus a delay), or keep the card mouse-only?
4. **Setting:** can users switch hover cards off?
