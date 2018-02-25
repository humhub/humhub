<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

use humhub\libs\Html;

/* @var $this \humhub\components\View */
/* @var $title string */
/* @var $filters array */
/* @var $block array */
/* @var $checked array */
/* @var $options array */
/* @var $filterClass boolean */
/* @var $linkOptions array */
/* @var $radio string|[] */
/* @var $reversedRadio [] */
?>

<?= Html::beginTag('div', $options) ?>
    <strong><?= $title ?></strong>
    <ul>
        <?php foreach ($block as $filterId): ?>
            <?php $linkOptions['id'] = $filterId ?>
            <?php if (is_array($radio) && isset($reversedRadio[$filterId])) : ?>
                <?php $linkOptions['data-filter-radio'] = $reversedRadio[$filterId]?>
            <?php elseif (is_string($radio)) : ?>
                <?php $linkOptions['data-filter-radio'] = $radio ?>
            <?php else: ?>
                <?php $linkOptions['data-filter-radio'] = null ?>
            <?php endif ?>
            <li>
                <?= Html::beginTag('a', $linkOptions) ?>
                    <i class="fa  <?= (in_array($filterId, $checked)) ? 'fa-check-square-o' : 'fa-square-o' ?>"></i> <?= $filters[$filterId]; ?>
                <?= Html::endTag('a') ?>
            </li>
        <?php endforeach; ?>
    </ul>
<?= Html::endTag('div') ?>
