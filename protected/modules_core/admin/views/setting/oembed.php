<div class="panel panel-default">
    <div class="panel-heading"><?php echo Yii::t('AdminModule.views_setting_oembed', '<strong>OEmbed</strong> Provider'); ?></div>
    <div class="panel-body">

        <p><?php echo HHtml::link(Yii::t('AdminModule.views_setting_oembed', 'Add new provider'), $this->createUrl('oembedEdit'), array('class' => 'btn btn-primary')); ?></p>


        <?php if (count($providers) != 0): ?>
            <p><strong><?php echo Yii::t('AdminModule.views_setting_oembed', 'Currently active providers:'); ?></strong></p>
            <ul>
                <?php foreach ($providers as $providerUrl => $providerOEmbedAPI) : ?>
                    <li><?php echo HHtml::postLink($providerUrl, $this->createUrl('oembedEdit'), array(), array('prefix' => $providerUrl)); ?></li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p><strong><?php echo Yii::t('AdminModule.views_setting_oembed', 'Currently no provider active!'); ?></strong></p>
        <?php endif; ?>



    </div>
</div>

