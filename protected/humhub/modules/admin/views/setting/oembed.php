<?php

use yii\helpers\Html;
use yii\helpers\Url;
?>
<div class="panel panel-default">
    <div class="panel-heading"><?php echo Yii::t('AdminModule.views_setting_oembed', '<strong>OEmbed</strong> Provider'); ?></div>
    <div class="panel-body">

        <p><?php echo Html::a(Yii::t('AdminModule.views_setting_oembed', 'Add new provider'), Url::to(['oembed-edit']), array('class' => 'btn btn-primary')); ?></p>


        <?php if (count($providers) != 0): ?>
            <p><strong><?php echo Yii::t('AdminModule.views_setting_oembed', 'Currently active providers:'); ?></strong></p>
            <ul>
                <?php foreach ($providers as $providerUrl => $providerOEmbedAPI) : ?>
                    <li><?php echo Html::a($providerUrl, Url::to(['oembed-edit', 'prefix' => $providerUrl]), array('data-method' => 'POST')); ?></li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p><strong><?php echo Yii::t('AdminModule.views_setting_oembed', 'Currently no provider active!'); ?></strong></p>
        <?php endif; ?>

    </div>
</div>

