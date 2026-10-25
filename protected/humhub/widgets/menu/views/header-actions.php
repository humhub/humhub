<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

use humhub\components\View;
use humhub\helpers\Html;
use humhub\widgets\Icon;
use humhub\widgets\menu\MenuLink;
use humhub\widgets\menu\HeaderActions;

/* @var $this View */
/* @var $menu HeaderActions */
/* @var $tools MenuLink[] */
/* @var $primary MenuLink|null */

$button = static function (MenuLink $entry, string $variant): string {
    $options = $entry->getHtmlOptions();
    // The link widget's generated id (`w12`) means nothing here; an id set deliberately is kept.
    if (isset($options['id']) && $options['id'] === $entry->getLink()->getId()) {
        unset($options['id']);
    }
    // A button class the entry carries for another look gives way to the role's look.
    $classes = array_filter(
        preg_split('/\s+/', implode(' ', (array)($options['class'] ?? [])), -1, PREG_SPLIT_NO_EMPTY),
        static fn(string $class) => $class !== 'btn' && !str_starts_with($class, 'btn-'),
    );
    $label = strip_tags((string)$entry->getLabel());
    $options['class'] = array_merge(['btn', 'btn-' . $variant, 'btn-icon-only'], $classes);
    $options['aria-label'] = $label;
    $options['title'] = $label;

    return Html::a(Icon::get($entry->getIcon()), $entry->getUrl(), $options);
};
?>
<?php foreach ($tools as $entry) : ?>
    <?= $button($entry, $menu->toolVariant) ?>
<?php endforeach; ?>
<?php if ($primary !== null) : ?>
    <?= $button($primary, 'accent') ?>
<?php endif; ?>
