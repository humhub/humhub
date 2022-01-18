<?php

use humhub\modules\installer\forms\SampleDataForm;
use humhub\modules\ui\form\widgets\ActiveForm;
use yii\bootstrap\Html;

?>
<div id="pretty-urls" class="panel panel-default animated fadeIn">

    <div class="panel-heading">
        <?= Yii::t('InstallerModule.base', '<strong>Pretty URLs</strong> configuration') ?>
    </div>

    <div class="panel-body">
        <p><?= Yii::t('InstallerModule.base', 'By default, the HumHub URL includes a index.php file part and looks like <a>https://example.com/index.php?r=dashboard%2Fdashboard</a>. Using the Pretty URL or URL Rewriting feature, shorter and more meaningful URLs can be created such as <a>https://temp.humhub.dev/dashboard</a>.'); ?></p>
        <p><?= Yii::t('InstallerModule.base', 'To enable this feature, both the HumHub configuration and, possibly, the WebServer configuration must be modified.'); ?></p>
        <p><?= Yii::t('InstallerModule.base', 'Modify the HumHub configuration file <code>/protected/config/common.php</code> and add following block:'); ?></p>

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

        <p><?= Yii::t('InstallerModule.base', 'In our documentation we get into <a href="{link}">Pretty URLs</a> in more detail. If you have trouble setting up the job scheduling described in this guide, please contact your provider to ask for support.', ['link' => 'https://docs.humhub.org/docs/admin/installation/#pretty-urls']); ?></p>
        <hr>

        <?= Html::a(Yii::t('base', 'Next'), Yii::$app->getModule('installer')->getNextConfigStepUrl(), ['class' => 'btn btn-primary']) ?>

    </div>
</div>


