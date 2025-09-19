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
 * @var $text ?string
 */
?>

<div<?= $id ? ' id="' . $id . '"' : ''?>
    class="hh-loader humhub-ui-loader d-flex justify-content-center align-items-center<?= $cssClass ? ' ' . $cssClass : '' ?><?= isset($show) && !$show ? ' d-none' : '' ?>">
    <span class="spinner-border" aria-hidden="true"></span>
    <span class="<?= $text === null ? 'visually-hidden' : 'ms-2' ?>" role="status">
        <strong><?= $text ?? Yii::t('base', 'Loading...') ?></strong>
    </span>
</div>
