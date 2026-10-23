# Entity Card

> Found in the design: People directory (people card), Spaces directory (space card), Marketplace
> (module card), Welcome wizard (people suggestions)
> · Core: `PeopleCard.vue`, `SpaceCard.vue` (shared `c-entity-card`), `ModuleCard.vue`
> (`c-module-card`), since 1.20
> · Technology: **Vue** (exists)

The card for one person, space or module in a [Card Directory](card-directory.md). It always has
the same five parts, so cards of different kinds look and line up the same. It shows enough to
decide (picture, name, short text, tags) and offers the next step (join, follow, add friend,
install) directly on the card.

![Entity card variants](images/entity-card-variants.svg)

## Anatomy

| # | Part | Notes |
|---|---|---|
| 1 | **Cover** | The banner image, or plain grey without one. The entity's image (80px) sits on top of it. A person's image shows the online state as a green ring. |
| 2 | **Stat pill** | Counts on the cover: friends or followers for people; followers and members for spaces. Each count opens the list in a dialog. |
| 3 | **Title and subtitle** | The name links to the profile, space or marketplace page. The subtitle is the job title, the module version, or for spaces what a module adds through the `space.card-subtitle` slot (the Enterprise theme shows the space category there). |
| 4 | **Body** | People: the profile fields chosen by the admin. Spaces and modules: the description. The body always keeps room for 3 lines, so cards in a row stay aligned. Descriptions are cut after 3 lines, and each profile field is one line with an ellipsis. |
| 5 | **Tags** | Up to 5 uppercase chips. Each one filters the directory by that tag. |
| 6 | **Footer** | The card's actions, sharing the full width. Icon-only actions (send message, changelog) are square and 38px high. |
| 7 | **Badges** | Modules only, top right of the cover: featured ★, update available (↑ 1.4.2) or the type (Official, Partner, Community, Professional). Featured and type filter the marketplace. |

## Variants

| Variant | Cover | Subtitle | Body | Footer |
|---|---|---|---|---|
| **People** | Profile banner, avatar with online ring, friends or followers pill | Job title | Admin-chosen profile fields, optional details template | Friendship (Add friend / Pending / Friends), Follow; modules add e.g. Send message |
| **Space** | Space banner, space image, followers and members pill | Via extension slot (Enterprise theme: category) | Description; "Archived" note for archived spaces | Membership (Join / Pending / Member), Follow |
| **Module** | No banner: module icon, badges | Version | Description, error message | Install / Buy / Update / Learn more, or a disabled "Installed" / "Not compatible"; ghost icon to the changelog or marketplace page |

## Relationship buttons

![Relationship buttons and card states](images/entity-card-states.svg)

| Relationship | States | What a click does |
|---|---|---|
| **Space membership** | Join → Pending → Member | Join: sends a request (asks for a reason if the space requires approval). Pending: offers to cancel the request. Member: offers to leave the space. |
| **Following** | Follow ↔ Following | Toggles at once, with no dialog |
| **Friendship** | Add friend → Pending → Friends | Add friend: sends a request. Pending: offers to cancel it. Friends: offers to remove the friend. |

- The **label shows the current state** ("Member"), and the confirm dialog says what a click would
  do ("Leave Marketing?").
- Changes that are hard to undo always confirm.
- "Pending" shows a turning hourglass.
- The button colour follows the state:
  - a dark primary button for the first step (Join, Add friend)
  - accent for Follow
  - grey for an established state (Member, Following, Friends)

## States

| State | Look |
|---|---|
| **Loading** | Skeleton with the card's geometry: cover, image, title, lines, action bar |
| **Actions loading** | Card content is shown, but the footer is a disabled placeholder until the viewer's state arrives |
| **No actions** | No footer, e.g. on your own people card or for guests |
| **Archived space** | "Archived" note (warning colour, archive icon) in the body |
| **Module update** | Update → Updating… (an accent arc travels round the card) → Updated! → the card fades to the new version. "Update all" queues every card. |
| **Search match** | Marketplace: matching text in the name and description is highlighted |

## Mobile

- One card per row at phone width, with the same content as on desktop.
- Footer actions keep sharing the width. Icon-only actions stay square.

## Accessibility

- The title is the card's only link to the entity. The cover image is decorative, so it isn't a
  second tab stop.
- Stat pills: buttons labelled with the full sentence ("19 followers"). When the list can't be
  opened, they are plain text with visually hidden text.
- Tags and badges: buttons with "Filter by …" as label and tooltip.
- Placeholders while loading are `disabled` and `aria-hidden`.
- Error messages on a module card use `role="alert"`.

## Implementation in core

**Exists:**

- **People and Space cards** share the entity card styles (`resources/scss/_entity-card.scss`,
  `.c-entity-card__cover/__pill/__header/__body/__tags/__footer`). They are `PeopleCard.vue` with
  `PeopleCardSkeleton.vue` and `SpaceCard.vue` with `SpaceCardSkeleton.vue`.
- **Module card:** `ModuleCard.vue` with its own styles (`.c-module-card`,
  `resources/scss/_marketplace.scss`).
- **Extension slots**, so modules add their own lines and actions without touching the card:
  - `user.card-subtitle` and `user.card-actions` (core entries: `friendship` 100, `follow` 200)
  - `space.card-subtitle` and `space.card-actions` (core entries: `membership` 100, `follow` 200)

  A module registers an entry with
  `registerSlotComponent('user.card-actions', 'MailCardAction', { id: 'mail', sortOrder: 300 })`.
- **Relationship buttons:** `MembershipButton.vue`, `FollowButton.vue`, `FriendshipButton.vue`
  and `UserFollowButton.vue`. The same components work outside the cards.

**Gaps compared to the design:**

| Gap | Proposal |
|---|---|
| Module card does not use the entity card | Move `ModuleCard` onto `c-entity-card` with a "no banner" cover variant and a `badges` area, so all directory cards share one geometry and one skeleton |
| Cover fallback is plain grey | The design generates a colour mesh from the space or user colour when there is no banner. Optional; decide if wanted. |
| Send message on people cards | Not in core. It belongs to the mail module through `user.card-actions`. |
| Tags vs. topics | Cards show profile and space tags. The design's chips look like topic chips. Keep tags, and confirm that topics are not meant. |

## Open questions

1. **One card base for modules too?** See the first gap. The recommendation is to unify.
2. **Generated covers:** do we want the generated colour mesh as cover fallback, or keep the plain
   grey?
3. **Body for people:** the admin chooses the profile fields shown. Is there a limit (e.g. 3
   lines), so cards stay the same height?
4. **Card click:** only the title is a link today. Should the whole card be clickable (with the
   buttons on top)?
