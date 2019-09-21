<?php
$this->pageTitle = Yii::t('UserModule.auth', 'Registration successful');
?>
<div class="modal-dialog modal-dialog-small animated fadeIn">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            <h4 class="modal-title" id="myModalLabel"><?php echo Yii::t('UserModule.auth', '<strong>Registration</strong> successful!'); ?></h4>
        </div>
        <div class="modal-body text-center">
            <p><?php echo Yii::t('UserModule.auth', 'Please check your email and follow the instructions!'); ?></p>
            <br>
            <a href="<?= \yii\helpers\Url::to(["/"]) ?>" data-pjax-prevent data-ui-loader class="btn btn-primary"><?php echo Yii::t('UserModule.auth', 'back to home') ?></a>
        </div>
    </div>
</div>



