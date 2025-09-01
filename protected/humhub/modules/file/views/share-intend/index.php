<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2025 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

use humhub\components\View;
use humhub\widgets\modal\Modal;
use humhub\widgets\modal\ModalButton;
use yii\helpers\Url;

/**
 * @var $this View
 * @var $shareTargets array
 */
?>

<?php Modal::beginDialog([
    'id' => 'share-intend-modal',
    'title' => Yii::t('FileModule.base', 'Share'),
    'footer' => ModalButton::cancel(),
]) ?>

    <?php foreach ($shareTargets as $target): ?>
        <a class="btn btn-primary d-grid gap-2" data-action-click="ui.modal.load"
           data-action-url="<?= Url::to([$target['route']]) ?>">
            <?= $target['title'] ?>
        </a>
    <?php endforeach; ?>

<?php Modal::endDialog() ?>
