<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2021 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

use humhub\helpers\Html;

/* @var $data array */
/* @var $filterInput string */
?>
<div class="<?= $data['wrapperClass'] ?>">
    <?php if (isset($data['title'])) : ?>
        <?= Html::tag('label', Html::encode($data['title']), [
            'for' => $data['inputId'],
            'class' => $data['titleClass'],
        ]) ?>
    <?php endif ?>
    <?= $filterInput ?>
</div>
