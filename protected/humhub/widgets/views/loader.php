<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

/**
 * @var $id string
 * @var $show bool
 * @var $cssClass string
 */
?>

<div<?= $id ? ' id="' . $id . '"' : ''?><?= (isset($show) && !$show) ? ' style="display:none;"' : '' ?>
    class="hh-loader humhub-ui-loader text-center<?= $cssClass ? ' ' . $cssClass : '' ?>">
    <div class="spinner-border" role="status">
        <span class="visually-hidden"><?= Yii::t('base', 'Loading...') ?></span>
    </div>
</div>
