<div class="container" style="text-align: center;">
    <div class="row">
        <div class="panel panel-default" style="max-width: 300px; margin: 0 auto 20px; text-align: left;">
            <div class="panel-heading"><?php echo Yii::t('UserModule.auth', 'Password recovery!'); ?></div>
            <div class="panel-body">
                <p><?php echo Yii::t('UserModule.base', 'We just send you an e-mail with a new password.'); ?></p>
                <a href="<?php echo $this->createUrl('//') ?>" class="btn btn-primary"><?php echo Yii::t('UserModule.auth', 'back to home') ?></a>
            </div>
        </div>
    </div>
</div>
