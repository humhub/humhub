<div class="modal-dialog modal-dialog-small animated fadeIn">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            <h4 class="modal-title" id="myModalLabel"><strong>Join</strong> the network</h4>
        </div>
        <div class="modal-body">
            <br/>

            <?php if ($canRegister) : ?>
                <div class="text-center">
                    <ul id="tabs" class="nav nav-tabs tabs-center" data-tabs="tabs">
                        <li class="<?php echo (!isset($_POST['AccountRegisterForm'])) ? "active" : ""; ?> tab-login"><a
                                href="#login"
                                data-toggle="tab"><?php echo Yii::t('SpaceModule.views_space_invite', 'Login'); ?></a>
                        </li>
                        <li class="<?php echo (isset($_POST['AccountRegisterForm'])) ? "active" : ""; ?> tab-register"><a
                                href="#register"
                                data-toggle="tab"><?php echo Yii::t('SpaceModule.views_space_invite', 'New user?'); ?></a>
                        </li>
                    </ul>
                </div>
                <br/>
            <?php endif; ?>


            <div class="tab-content">
                <div class="tab-pane <?php echo (!isset($_POST['AccountRegisterForm'])) ? "active" : ""; ?>" id="login">

                    <?php
                    $form = $this->beginWidget('CActiveForm', array(
                        'id' => 'account-login-form',
                        'enableAjaxValidation' => false,
                    ));
                    ?>


                    <p><?php echo Yii::t('UserModule.views_auth_login', "If you're already a member, please login with your username/email and password."); ?></p>

                    <div class="form-group">
                        <?php echo $form->textField($model, 'username', array('class' => 'form-control', 'id' => 'login_username', 'placeholder' => Yii::t('UserModule.views_auth_login', 'username or email'))); ?>
                        <?php echo $form->error($model, 'username'); ?>
                    </div>

                    <div class="form-group">
                        <?php echo $form->passwordField($model, 'password', array('class' => 'form-control', 'id' => 'login_password', 'placeholder' => Yii::t('UserModule.views_auth_login', 'password'))); ?>
                        <?php echo $form->error($model, 'password'); ?>
                    </div>

                    <div class="form-group">
                        <div class="checkbox">
                            <label>
                                <?php echo $form->checkBox($model, 'rememberMe'); ?> <?php echo Yii::t('UserModule.views_auth_login', 'Remember me next time'); ?>
                            </label>
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-md-4">
                            <?php
                            echo HHtml::ajaxSubmitButton(Yii::t('UserModule.views_auth_login', 'Sign in'), array('//user/auth/login'), array(
                                'type' => 'POST',
                                'success' => 'function(html){ $("#globalModal").html(html); }',
                                    ), array('class' => 'btn btn-primary', 'id' => 'loginBtn'));
                            ?>
                        </div>
                        <div class="col-md-8 text-right">
                            <small>
                                <?php echo Yii::t('UserModule.views_auth_login', 'Forgot your password?'); ?>
                                <br/>
                                <?php
                                echo HHtml::ajaxLink(Yii::t('UserModule.views_auth_login', 'Create a new one.'), array('//user/auth/recoverPassword'), array(
                                    'type' => 'POST',
                                    'success' => 'function(html){ $("#globalModal").html(html); }',
                                        ), array('class' => '', 'id' => 'recoverPasswordBtn'));
                                ?>
                            </small>
                        </div>
                    </div>

                    <?php $this->endWidget(); ?>
                </div>

                <?php if ($canRegister) : ?>
                    <div class="tab-pane <?php echo (isset($_POST['AccountRegisterForm'])) ? "active" : ""; ?>"
                         id="register">

                        <p><?php echo Yii::t('UserModule.views_auth_login', "Don't have an account? Join the network by entering your e-mail address."); ?></p>
                        <?php
                        $form = $this->beginWidget('CActiveForm', array(
                            'id' => 'account-register-form',
                            'enableAjaxValidation' => false,
                        ));
                        ?>

                        <div class="form-group">
                            <?php echo $form->textField($registerModel, 'email', array('class' => 'form-control', 'id' => 'register-email', 'placeholder' => Yii::t('UserModule.views_auth_login', 'email'))); ?>
                            <?php echo $form->error($registerModel, 'email'); ?>
                        </div>
                        <hr>

                        <?php
                        echo HHtml::ajaxSubmitButton(Yii::t('UserModule.views_auth_login', 'Register'), array('//user/auth/login'), array(
                            'type' => 'POST',
                            'success' => 'function(html){ $("#globalModal").html(html); }',
                                ), array('class' => 'btn btn-primary', 'id' => 'registerBtn'));
                        ?>

                        <?php $this->endWidget(); ?>

                    </div>
                <?php endif; ?>
            </div>
        </div>

    </div>

</div>

<script type="text/javascript">
    // Replace the standard checkbox and radio buttons
    $('body').find(':checkbox, :radio').flatelements();


    $(document).ready(function() {
        $('#login_username').focus();

    });

    $('.tab-register a').on('shown.bs.tab', function(e) {
        $('#register-email').focus();
    })

    $('.tab-login a').on('shown.bs.tab', function(e) {
        $('#login_username').focus();
    })

</script>