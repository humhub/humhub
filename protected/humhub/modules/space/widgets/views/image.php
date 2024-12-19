<?php

use humhub\helpers\Html;
use humhub\modules\space\models\Space;

/**
 * @var $acronym string
 * @var $space Space
 * @var $linkOptions array
 * @var $acronymHtmlOptions array
 * @var $imageHtmlOptions array
 * @var $link bool
 * @var $isDefaultImage bool
 */

$imageUrl = $space->getProfileImage()->getUrl();
?>

<?php if ($link): ?>
    <?= Html::beginTag('a', $linkOptions) ?>
<?php endif; ?>

<?= Html::beginTag('div', $acronymHtmlOptions) ?>
<?= $acronym ?>
<?= Html::endTag('div') ?>
<?= Html::img($imageUrl, $imageHtmlOptions) ?>

<?php if ($link): ?>
    <?= Html::endTag('a') ?>
<?php endif; ?>
