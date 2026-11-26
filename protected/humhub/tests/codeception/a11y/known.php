<?php

/**
 * Known violations of the accessibility checks (A11yCest): reported apart from new ones, and no failure with
 * `HUMHUB_A11Y=fail`. Each entry names the axe rule, a part of the element's selector and why it is known (the
 * planned fix), optionally the modes it is known in (`light`, `dark`, `light-hc`, `dark-hc`; all by default): the
 * same violation in another mode is new. An entry is removed with its fix.
 *
 * @see docs/develop/intro-testing.md
 */

return [
    // WCAG 2.5.8: targets below 24 × 24px
    ['rule' => 'target-size', 'target' => '[data-contentcontainer-id=',
        'reason' => '2.5.8: the author link in a stream entry\'s header is 19px high'],
    ['rule' => 'target-size', 'target' => '#search-menu',
        'reason' => '2.5.8: the search toggle beside the open account menu'],

    // WCAG 2.5.3: the visible text is not part of the accessible name
    ['rule' => 'label-content-name-mismatch', 'target' => '#space-menu',
        'reason' => '2.5.3: the space menu\'s button shows the space\'s initials, its name is "My spaces"'],
    ['rule' => 'label-content-name-mismatch', 'target' => 'a[aria-label="Space ',
        'reason' => '2.5.3: a space image link shows the initials, its name is the space\'s name'],
    ['rule' => 'label-content-name-mismatch', 'target' => 'a[href$="space-',
        'reason' => '2.5.3: a space image link shows the initials, its name is the space\'s name'],

    // WCAG 1.4.3
    ['rule' => 'color-contrast', 'target' => 'space-profile-acronym-', 'modes' => ['light-hc', 'dark-hc'],
        'reason' => '1.4.3: space initials in high contrast (e.g. #464646 on #757575)'],
];
