<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2021 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

use humhub\modules\ui\icon\widgets\Icon;

/* @var string $editUrl */
/* @var string $loadUrl */
?>
<li>
    <a href="#" class="comment-edit-link" data-action-click="edit"
       data-action-url="<?= $editUrl ?>">
        <?= Icon::get('edit') ?> <?= Yii::t('CommentModule.base', 'Edit') ?>
    </a>
    <a href="#" class="comment-cancel-edit-link" data-action-click="cancelEdit"
       data-action-url="<?= $loadUrl ?>" style="display:none;">
        <?= Icon::get('edit') ?> <?= Yii::t('CommentModule.base', 'Cancel Edit') ?>
    </a>
</li>