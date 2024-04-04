<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2021 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

/* @var $data array */
/* @var $filterInput string */
?>

<div class="<?= $data['wrapperClass'] ?>">
    <?php if (isset($data['title'])) : ?>
        <div class="<?= $data['titleClass'] ?>"><?= $data['title'] ?></div>
    <?php endif; ?>
    <?= $filterInput ?>
</div>
