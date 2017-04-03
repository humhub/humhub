<?php

use yii\widgets\ActiveForm;
use yii\helpers\Url;
use humhub\modules\user\widgets\AuthChoice;
?>
<div class="modal-dialog modal-dialog-small animated fadeIn">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            <h4 class="modal-title" id="myModalLabel"><?php echo Yii::t('UserModule.views_auth_login', '<strong>Join</strong> the network'); ?></h4>
        </div>
        <div class="modal-body">
            <br/>

            <?php if ($canRegister) : ?>
                <div class="text-center">
                    <ul id="tabs" class="nav nav-tabs tabs-center" data-tabs="tabs">
                        <li class="<?php echo (!isset($_POST['Invite'])) ? "active" : ""; ?> tab-login"><a
                                href="#login"
                                data-toggle="tab"><?php echo Yii::t('SpaceModule.views_space_invite', 'Login'); ?></a>
                        </li>
                        <li class="<?php echo (isset($_POST['Invite'])) ? "active" : ""; ?> tab-register"><a
                                href="#register"
                                data-toggle="tab"><?php echo Yii::t('SpaceModule.views_space_invite', 'New user?'); ?></a>
                        </li>
                    </ul>
                </div>
                <br/>
            <?php endif; ?>


            <div class="tab-content">
                <div class="tab-pane <?php echo (!isset($_POST['Invite'])) ? "active" : ""; ?>" id="login">

                    <?php if (AuthChoice::hasClients()): ?>
                        <?= AuthChoice::widget([]) ?>
                    <?php else: ?>
                        <?php if ($canRegister) : ?>
                            <p><?php echo Yii::t('UserModule.views_auth_login', "If you're already a member, please login with your username/email and password."); ?></p>
                        <?php else: ?>
                            <p><?php echo Yii::t('UserModule.views_auth_login', "Please login with your username/email and password."); ?></p>
                        <?php endif; ?>                    <?php endif; ?>

                    <?php $form = ActiveForm::begin(['enableClientValidation' => false]); ?>
                    <?php echo $form->field($model, 'username')->textInput(['id' => 'login_username', 'placeholder' => Yii::t('UserModule.views_auth_login', 'username or email')]); ?>
                    <?php echo $form->field($model, 'password')->passwordInput(['id' => 'login_password', 'placeholder' => Yii::t('UserModule.views_auth_login', 'password')]); ?>
                    <?php echo $form->field($model, 'rememberMe')->checkbox(); ?>
                    <hr>
                    <div class="row">
                        <div class="col-md-4">
                            <button href="#" id="loginBtn" data-ui-loader type="submit" class="btn btn-primary" data-action-click="ui.modal.submit" data-action-url="<?= Url::to(['/user/auth/login']) ?>">
                                <?= Yii::t('UserModule.views_auth_login', 'Sign in') ?>
                            </button>

                        </div>
                        <div class="col-md-8 text-right">
                            <small>
                                <?= Yii::t('UserModule.views_auth_login', 'Forgot your password?'); ?>
                                <br/>
                                <a id="recoverPasswordBtn" href="#" data-action-click="ui.modal.load" data-action-url="<?= Url::to(['/user/password-recovery']) ?>">
                                    <?= Yii::t('UserModule.views_auth_login', 'Create a new one.') ?>
                                </a>
                            </small>
                        </div>
                    </div>

                    <?php ActiveForm::end(); ?>
                </div>

                <?php if ($canRegister) : ?>
                    <div class="tab-pane <?= (isset($_POST['Invite'])) ? "active" : ""; ?>"
                         id="register">

                        <p><?= Yii::t('UserModule.views_auth_login', "Don't have an account? Join the network by entering your e-mail address."); ?></p>
                        <?php $form = ActiveForm::begin(['enableClientValidation' => false]); ?>

                        <?= $form->field($invite, 'email')->input('email', ['id' => 'register-email', 'placeholder' => Yii::t('UserModule.views_auth_login', 'email')]); ?>
                        <hr>

                        <a href="#" class="btn btn-primary" data-ui-loader data-action-click="ui.modal.submit" data-action-url="<?= Url::to(['/user/auth/login']) ?>">
                            <?= Yii::t('UserModule.views_auth_login', 'Register') ?>
                        </a>

                        <?php ActiveForm::end(); ?>

                    </div>
                <?php endif; ?>
            </div>
        </div>

    </div>

</div>

<script type="text/javascript">
    $(document).on('ready pjax:success', function () {
        $('#login_username').focus();

    });

    $('.tab-register a').on('shown.bs.tab', function (e) {
        $('#register-email').focus();
    })

    $('.tab-login a').on('shown.bs.tab', function (e) {
        $('#login_username').focus();
    })

</script>