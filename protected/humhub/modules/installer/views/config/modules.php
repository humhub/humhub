<?php

use yii\widgets\ActiveForm;
use yii\bootstrap\Html;
?>
<div id="name-form" class="panel panel-default animated fadeIn">

    <div class="panel-heading">
        <?php echo Yii::t('InstallerModule.base', 'Recommended <strong>Modules</strong>'); ?>
    </div>

    <div class="panel-body">

        TBD

        <hr>

        <?php echo Html::a(Yii::t('base', 'Next'), ['modules', 'ok' => 1], array('class' => 'btn btn-primary')); ?>
    </div>
</div>


