<?php
/**
 * Login and registration page by AuthController
 *
 * @property CFormModel $model is the login form.
 * @property CFormModel $registerModel is the registration form.
 * @property Boolean $canRegister indicates that anonymous registrations are enabled.
 *
 * @package humhub.modules_core.user.views
 * @since 0.5
 */
?>


<div class="container" style="text-align: center;">
    <h1><?php echo Yii::app()->name; ?></h1>
    <br>

    <div class="panel panel-default" style="max-width: 300px; margin: 0 auto 20px; text-align: left;">

        <div class="panel-heading"><?php echo Yii::t('UserModule.auth', 'Please sign in'); ?></div>

        <div class="panel-body">
            <?php
            $form = $this->beginWidget('CActiveForm', array(
                'id' => 'account-login-form',
                'enableAjaxValidation' => false,
            ));
            ?>

            <div class="form-group">
                <?php echo $form->textField($model, 'username', array('class' => 'form-control', 'placeholder' => Yii::t('UserModule.auth', 'username or email'))); ?>
                <?php echo $form->error($model, 'username'); ?>
            </div>

            <div class="form-group">
                <?php echo $form->passwordField($model, 'password', array('class' => 'form-control', 'placeholder' => Yii::t('UserModule.auth', 'password'))); ?>
                <?php echo $form->error($model, 'password'); ?>
            </div>

            <div class="checkbox">
                <label>
                    <?php echo $form->checkBox($model, 'rememberMe'); ?> <?php echo Yii::t('UserModule.auth', 'Remember me next time'); ?>
                </label>
            </div>

            <hr>
            <?php echo CHtml::submitButton(Yii::t('UserModule.base', 'Sign in'), array('class' => 'btn btn-large btn-primary')); ?><br>

            <?php $this->endWidget(); ?>

        </div>


    </div>
    <a href="<?php echo $this->createUrl('//user/auth/recoverPassword'); ?>"><?php echo Yii::t('UserModule.auth', 'Forgot your password?') ?></a>
<br><br><br>

    <?php if ($canRegister) : ?>
        <div class="panel panel-default" style="max-width: 300px; margin: 0 auto 20px; text-align: left;">

            <div class="panel-heading"><?php echo Yii::t('UserModule.auth', 'Registration') ?></div>

            <div class="panel-body">

                <p><?php echo Yii::t('UserModule.base', 'Please enter your e-mail to join the network.'); ?></p>
                <?php
                $form = $this->beginWidget('CActiveForm', array(
                    'id' => 'account-register-form',
                    'enableAjaxValidation' => true,
                ));
                ?>

                <div class="form-group">
                    <?php echo $form->textField($registerModel, 'email', array('class' => 'form-control', 'placeholder' => Yii::t('UserModule.auth', 'email'))); ?>
                    <?php echo $form->error($registerModel, 'email'); ?>
                </div>
                <hr>
                <?php echo CHtml::submitButton(Yii::t('UserModule.base', 'Register'), array('class' => 'btn btn-primary')); ?>

                <?php $this->endWidget(); ?>
            </div>
        </div>
    <?php endif; ?>
</div>


