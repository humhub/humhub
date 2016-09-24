<?php

use yii\helpers\Html;
use yii\helpers\Url;

$this->pageTitle = Yii::t('base', 'Error');
?>
<div class="container">
    <div class="panel panel-default">
        <div class="panel-heading">
            <?php echo Yii::t('base', "Oooops..."); ?> <?php echo Yii::t('base', "It looks like you may have taken the wrong turn."); ?>
        </div>
        <div class="panel-body">

            <div class="error">
                <h2><?php echo Html::encode($message); ?></h2>
            </div>

            <hr>
            <a href="<?php echo Url::home() ?>" class="btn btn-primary"><?php echo Yii::t('base', 'Back to dashboard'); ?></a>
        </div>
    </div>
</div>