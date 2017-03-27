<?php

use yii\helpers\Html;
use yii\helpers\Url;
?>

<?php $this->beginContent('@admin/views/setting/_advancedLayout.php') ?>

<p class="pull-right">
    <?= Html::a(Yii::t('AdminModule.views_setting_oembed', 'Add new provider'), Url::to(['oembed-edit']), ['class' => 'btn btn-success']); ?>
</p>

<h4><?= Yii::t('AdminModule.views_setting_oembed', 'Enabled OEmbed providers'); ?></h4>

<?php if (count($providers) != 0): ?>
    <ul>
        <?php foreach ($providers as $providerUrl => $providerOEmbedAPI) : ?>
            <li><?= Html::a($providerUrl, Url::to(['oembed-edit', 'prefix' => $providerUrl]), ['data-method' => 'POST']); ?></li>
        <?php endforeach; ?>
    </ul>
<?php else: ?>
    <p><strong><?= Yii::t('AdminModule.views_setting_oembed', 'Currently no provider active!'); ?></strong></p>
<?php endif; ?>

<?php $this->endContent(); ?>
