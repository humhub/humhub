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
    <h1 id="app-title" class="animated fadeIn"><?php echo Yii::app()->name; ?></h1>
    <br>

    <div class="panel panel-default animated bounceIn" id="login-form" style="max-width: 300px; margin: 0 auto 20px; text-align: left;">

        <div class="panel-heading"><?php echo Yii::t('UserModule.auth', '<strong>Please</strong> sign in'); ?></div>

        <div class="panel-body">
            <?php
            $form = $this->beginWidget('CActiveForm', array(
                'id' => 'account-login-form',
                'enableAjaxValidation' => false,
            ));
            ?>

            <p><?php echo Yii::t('UserModule.auth', "If you're already a member, please login with your username/email and password."); ?></p>

            <div class="form-group">
                <?php echo $form->textField($model, 'username', array('class' => 'form-control', 'id' => 'login_username', 'placeholder' => Yii::t('UserModule.auth', 'username or email'))); ?>
                <?php echo $form->error($model, 'username'); ?>
            </div>

            <div class="form-group">
                <?php echo $form->passwordField($model, 'password', array('class' => 'form-control', 'id' => 'login_password', 'placeholder' => Yii::t('UserModule.auth', 'password'))); ?>
                <?php echo $form->error($model, 'password'); ?>
            </div>

            <div class="checkbox">
                <label>
                    <?php echo $form->checkBox($model, 'rememberMe'); ?> <?php echo Yii::t('UserModule.auth', 'Remember me next time'); ?>
                </label>
            </div>

            <hr>
            <div class="row">
                <div class="col-md-4">
                    <?php echo CHtml::submitButton(Yii::t('UserModule.base', 'Sign in'), array('class' => 'btn btn-large btn-primary')); ?>
                </div>
                <div class="col-md-8 text-right">
                    <small>
                        <?php echo Yii::t('UserModule.auth', 'Forgot your password?'); ?>
                        <a href="<?php echo $this->createUrl('//user/auth/recoverPassword'); ?>"><br><?php echo Yii::t('UserModule.auth', 'Create a new one.') ?></a>
                    </small>
                </div>
            </div>

            <?php $this->endWidget(); ?>

        </div>

    </div>

    <br>

    <?php if ($canRegister) : ?>
        <div id="register-form" class="panel panel-default animated bounceInLeft"
             style="max-width: 300px; margin: 0 auto 20px; text-align: left;">

            <div class="panel-heading"><?php echo Yii::t('UserModule.auth', '<strong>Sign</strong> up') ?></div>

            <div class="panel-body">

                <p><?php echo Yii::t('UserModule.base', "Don't have an account? Join the network by entering your e-mail address."); ?></p>
                <?php
                $form = $this->beginWidget('CActiveForm', array(
                    'id' => 'account-register-form',
                    'enableAjaxValidation' => false,
                ));
                ?>

                <div class="form-group">
                    <?php echo $form->textField($registerModel, 'email', array('class' => 'form-control', 'id' => 'register-email', 'placeholder' => Yii::t('UserModule.auth', 'email'))); ?>
                    <?php echo $form->error($registerModel, 'email'); ?>
                </div>
                <hr>
                <?php echo CHtml::submitButton(Yii::t('UserModule.base', 'Register'), array('class' => 'btn btn-primary')); ?>

                <?php $this->endWidget(); ?>
            </div>
        </div>

    <?php endif; ?>

</div>

<script type="text/javascript">
    $(function () {
        // set cursor to login field
        $('#login_username').focus();
    })

    // Shake panel after wrong validation
    <?php if ($form->errorSummary($model) != null) { ?>
    $('#login-form').removeClass('bounceIn');
    $('#login-form').addClass('shake');
    $('#register-form').removeClass('bounceInLeft');
    $('#app-title').removeClass('fadeIn');
    <?php } ?>

    // Shake panel after wrong validation
    <?php if ($form->errorSummary($registerModel) != null) { ?>
    $('#register-form').removeClass('bounceInLeft');
    $('#register-form').addClass('shake');
    $('#login-form').removeClass('bounceIn');
    $('#app-title').removeClass('fadeIn');
    <?php } ?>

</script>


