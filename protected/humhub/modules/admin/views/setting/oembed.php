<?php

use yii\helpers\Html;
use yii\helpers\Url;
?>

<?php $this->beginContent('@admin/views/setting/_advancedLayout.php') ?>

<p class="pull-right"><?php echo Html::a(Yii::t('AdminModule.views_setting_oembed', 'Add new provider'), Url::to(['oembed-edit']), array('class' => 'btn btn-success')); ?></p>

<h4><?php echo Yii::t('AdminModule.views_setting_oembed', 'Enabled OEmbed providers'); ?></h4>


<?php if (count($providers) != 0): ?>
    <ul>
        <?php foreach ($providers as $providerUrl => $providerOEmbedAPI) : ?>
            <li><?php echo Html::a($providerUrl, Url::to(['oembed-edit', 'prefix' => $providerUrl]), array('data-method' => 'POST')); ?></li>
        <?php endforeach; ?>
    </ul>
<?php else: ?>
    <p><strong><?php echo Yii::t('AdminModule.views_setting_oembed', 'Currently no provider active!'); ?></strong></p>
<?php endif; ?>

<?php $this->endContent(); ?>
