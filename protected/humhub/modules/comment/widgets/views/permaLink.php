<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2021 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

use humhub\libs\Html;
use humhub\modules\ui\icon\widgets\Icon;

/* @var string $permaUrl */
/* @var string $modalWindowTitle */
?>
<li>
    <a href="<?= $permaUrl ?>"
       data-action-click="content.permalink" data-content-permalink="<?= $permaUrl ?>"
       data-content-permalink-title="<?= Html::encode($modalWindowTitle) ?>">
        <?= Icon::get('link') ?> <?= Yii::t('CommentModule.base', 'Permalink') ?>
    </a>
</li>