<?php

use yii\bootstrap\Html;
use yii\helpers\Url;

?>
<div id="pretty-urls" class="panel panel-default animated fadeIn">

    <div class="panel-heading">
        <?= Yii::t('InstallerModule.base', '<strong>Pretty URLs</strong> configuration') ?>
    </div>

    <div class="panel-body">
        <p><?= Yii::t('InstallerModule.base', 'By default, the HumHub URL includes a <code>index.php</code> file part and looks like <a>https://example.com/index.php?r=dashboard%2Fdashboard</a>. Using the Pretty URL or URL Rewriting feature, shorter and more meaningful URLs can be created such as <a>https://temp.humhub.dev/dashboard</a>.'); ?></p>
        <p><?= Yii::t('InstallerModule.base', 'To enable this feature, both the HumHub configuration and, possibly, the WebServer configuration must be modified.'); ?></p>
        <p><?= Yii::t('InstallerModule.base', 'Modify the HumHub configuration file <code>{DOCUMENT_ROOT}/protected/config/common.php</code> and add following block:', ['DOCUMENT_ROOT' => $_SERVER['DOCUMENT_ROOT']]); ?></p>

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

        <p><?= Yii::t('InstallerModule.base', 'In our documentation we get into <a href="{link}">Pretty URLs</a> in more detail.', ['link' => 'https://docs.humhub.org/docs/admin/installation/#pretty-urls']); ?></p>
        <hr>

        <?= Html::a(Yii::t('base', 'Next'), Url::to(['/installer/config/index']), ['class' => 'btn btn-primary']) ?>

    </div>
</div>


