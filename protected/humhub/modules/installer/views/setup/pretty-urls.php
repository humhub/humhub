<?php

use yii\bootstrap\Html;
use yii\helpers\Url;

?>
<div id="pretty-urls" class="panel panel-default animated fadeIn">

    <div class="panel-heading">
        <?= Yii::t('InstallerModule.base', '<strong>Pretty URLs</strong> configuration') ?>
    </div>

    <div class="panel-body">
        <p><?= Yii::t('InstallerModule.base', 'Per default, the HumHub URL includes an <code>index.php</code> file in the address, resulting in a URL that looks like "<a>https://example.com/index.php?r=dashboard%2Fdashboard</a>" for example. By using the Pretty URL or URL Rewriting feature, it is possible to create shorter and easier to understand URLs, such as "<a>https://example.com/dashboard</a>".'); ?></p>
        <p><?= Yii::t('InstallerModule.base', 'In order to activate this feature, it is necessary to edit the HumHub configuration and, potentially, the configuration of the WebServer.'); ?></p>
        <p><?= Yii::t('InstallerModule.base', 'On HumHub\'s side, you will need to edit the configuration file <code>{configFile}</code> and add the following block:', ['{configFile}' => Yii::getAlias('@config/common.php')]); ?></p>

        <kbd style="display: block; padding: 0.75rem 1rem;">
            <div>
                'components' => [<br>
                <div style="padding-left: 1.75rem;">
                    'urlManager' => [<br>
                    <div style="padding-left: 1.75rem;">
                        'showScriptName' => false,<br>
                        'enablePrettyUrl' => true<br>
                    </div>
                    ]<br>
                </div>
                ]
            </div>
        </kbd>
        <br>

        <p><?= Yii::t('InstallerModule.base', 'Our documentation provides a more detailed look into <a href="{link}" target="_blank">Pretty URLs</a>.', ['link' => 'https://docs.humhub.org/docs/admin/installation/#pretty-urls']); ?></p>
        <hr>

        <?= Html::a(Yii::t('base', 'Next'), ['finalize'], ['class' => 'btn btn-primary']) ?>

    </div>
</div>


