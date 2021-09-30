<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2021 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

use humhub\modules\ui\icon\widgets\Icon;

/* @var string $deleteUrl */
?>
<li>
    <a href="#" data-action-click="delete"
       data-content-delete-url="<?= $deleteUrl ?>">
        <?= Icon::get('delete') ?> <?= Yii::t('CommentModule.base', 'Delete') ?>
    </a>
</li>