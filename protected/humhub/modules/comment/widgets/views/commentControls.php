<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2021 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

use humhub\libs\Html;
use humhub\modules\ui\icon\widgets\Icon;
use humhub\modules\ui\menu\MenuEntry;

/* @var MenuEntry[] $entries */
/* @var array $options */
?>

<div class="comment-entry-loader pull-right"></div>
<?= Html::beginTag('ul', $options) ?>
    <li class="dropdown ">
        <a class="dropdown-toggle" data-toggle="dropdown" href="#"
           aria-label="<?= Yii::t('base', 'Toggle comment menu'); ?>" aria-haspopup="true">
            <?= Icon::get('dropdownToggle') ?>
        </a>

        <ul class="dropdown-menu pull-right">
            <?php foreach ($entries as $entry) : ?>
                <li>
                    <?= $entry->render() ?>
                </li>
            <?php endforeach; ?>
        </ul>
    </li>
<?= Html::endTag('ul')?>
