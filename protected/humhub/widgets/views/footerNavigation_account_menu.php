<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

use humhub\helpers\Html;
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
    <li class="dropdown-divider d-none d-sm-block d-md-none"></li>
    <?php foreach ($entries as $k => $entry): ?>
        <?php if ($entry instanceof MenuLink): ?>
            <li class="d-none d-sm-block d-md-none footer-nav-entry">
                <?= Html::a($entry->getIcon() . ' ' . $entry->getLabel(), $entry->getUrl(), $entry->getHtmlOptions()); ?>
            </li>
        <?php endif; ?>
    <?php endforeach; ?>
<?php endif; ?>
