<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

use humhub\libs\Html;
use humhub\modules\ui\menu\MenuLink;

/* @var $this \humhub\modules\ui\view\components\View */
/* @var $entries MenuLink[] */
/* @var $options array */
/* @var $menu \humhub\widgets\FooterMenu */


/**
 * NOTE: This template is used only in mobile view ports!
 */

?>

<?php if (!empty($entries)): ?>
    <li class="divider visible-sm visible-xs"></li>
    <?php foreach ($entries as $k => $entry): ?>
        <?php if ($entry instanceof MenuLink): ?>
            <li class="visible-sm visible-xs footer-nav-entry">
                <?= Html::a($entry->getIcon() . ' ' . $entry->getLabel(), $entry->getUrl(), $entry->getHtmlOptions()); ?>
            </li>
        <?php endif; ?>
    <?php endforeach; ?>
<?php endif; ?>
