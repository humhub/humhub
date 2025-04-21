<?php

use yii\bootstrap\Html;
use yii\helpers\Url;

?>
<div id="pretty-urls" class="panel panel-default animated fadeIn">

    <div class="panel-heading">
        <?= Yii::t('InstallerModule.base', '<strong>Pretty URLs</strong>') ?>
    </div>

    <div class="panel-body">
        <p><?= Yii::t('InstallerModule.base', 'In the installation folder, locate the <strong>.env.example</strong> file and copy its contents into the <strong>.env</strong> file. Next, find the <strong>Pretty URLs</strong> block and uncomment it by removing the <strong>#</strong> symbol.'); ?></p>

        <kbd style="display: block; padding: 0.75rem 1rem;">
            <div>
            #--- Pretty URLs (Recommended) <br>
            #HUMHUB_CONFIG__COMPONENTS__URL_MANAGER__SHOW_SCRIPT_NAME=false<br>
            #HUMHUB_CONFIG__COMPONENTS__URL_MANAGER__ENABLE_PRETTY_URL=true<br>
            </div>
        </kbd>
        <br>

        <kbd style="display: block; padding: 0.75rem 1rem;">
            <div>
            #--- Pretty URLs (Recommended) <br>
            HUMHUB_CONFIG__COMPONENTS__URL_MANAGER__SHOW_SCRIPT_NAME=false<br>
            HUMHUB_CONFIG__COMPONENTS__URL_MANAGER__ENABLE_PRETTY_URL=true<br>
            </div>
        </kbd>
        <br>

        <p><?= Yii::t('InstallerModule.base', 'For more information on this topic, please refer to our <a href="{link}" target="_blank">documentation</a>.', ['link' => 'https://docs.humhub.org/docs/admin/installation/#pretty-urls']); ?></p>
        <hr>

        <?= Html::a(Yii::t('base', 'Next'), ['finalize'], ['class' => 'btn btn-primary']) ?>

    </div>
</div>


