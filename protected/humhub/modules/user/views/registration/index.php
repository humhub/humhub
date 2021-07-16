<?php

use yii\helpers\Html;

$this->pageTitle = Yii::t('UserModule.auth', 'Create Account');
?>

<div class="container" style="overflow: visible">
    <div class="row">
        <div id="create-account-form" class="panel panel-default panel-login">
            <div class="user-icon">
                <i class="fa fa-user-o" aria-hidden="true"></i>
            </div>

            <div class="panel-heading"><?php echo Yii::t('UserModule.auth', '<strong>Account</strong> registration'); ?></div>
            <div class="panel-body">
                <?php $form = \yii\bootstrap\ActiveForm::begin(['id' => 'registration-form', 'enableClientValidation' => false]); ?>
                <?= $hForm->render($form); ?>

                <div class="clubs-list-container">
                    <input id="label-clubs" readonly placeholder="Choose a club">
                    <div class="drop-down-icon">
                        <i class="fa fa-angle-down" aria-hidden="true"></i>
                    </div>
                    <select name="clubs" id="clubs-list" size="4">
                        <option value="Berlin" class="club-option">Berlin</option>
                        <option value="Bremen" class="club-option">Bremen</option>
                        <option value="Dortmund" class="club-option">Dortmund</option>
                        <option value="Dresden" class="club-option">Dresden</option>
                        <option value="Frankfurt" class="club-option">Frankfurt</option>
                        <option value="Freiburg" class="club-option">Freiburg</option>
                        <option value="Gelsenkirchen" class="club-option">Gelsenkirchen</option>
                        <option value="Hamburg" class="club-option">Hamburg</option>
                        <option value="Hannover" class="club-option">Hannover</option>
                        <option value="Monchengladbach" class="club-option">Monchengladbach</option>
                        <option value="Munchen" class="club-option">Munchen</option>
                        <option value="Nuremberg" class="club-option">Nuremberg</option>
                        <option value="HamburgPirates" class="club-option">HamburgPirates</option>
                        <option value="Wolfsburg" class="club-option">Wolfsburg</option>
                    </select>
                </div>

                <?php \yii\bootstrap\ActiveForm::end(); ?>
            </div>
        </div>
    </div>
</div>

<script <?= \humhub\libs\Html::nonce() ?>>
    $(function() {
        // set cursor to login field
        $('#User_username').focus();

        // set user time zone val
        $('#user-time_zone').val(Intl.DateTimeFormat().resolvedOptions().timeZone);

        // Set placeholder
        $('#user-username').attr('placeholder', 'Username');
        $('#password-newpassword').attr('placeholder', 'New password');
        $('#password-newpasswordconfirm').attr('placeholder', 'Confirm password');
        $('#profile-firstname').attr('placeholder', 'First name');
        $('#profile-lastname').attr('placeholder', 'Last name');

        $('#user-username').attr('maxlength', '50');
        $('#password-newpassword').attr('maxlength', '25');
        $('#password-newpasswordconfirm').attr('maxlength', '25');
        $('#profile-firstname').attr('maxlength', '50');
        $('#profile-lastname').attr('maxlength', '50');

        $('#label-clubs').click(function() {
            $('#clubs-list').attr('style', 'display: block');
        })

        $(document).click(function(event) {
            if ($(event.target).closest("#label-clubs").length == 0) {
                $('#clubs-list').attr('style', 'display:none');
            }

        })

        $('.club-option').click(function(event) {
            $('#label-clubs').val(event.target.value)
        })
    })

    // Shake panel after wrong validation
    <?php foreach ($hForm->models as $model) : ?>
        <?php if ($model->hasErrors()) : ?>
            $('#create-account-form').removeClass('bounceIn');
            $('#create-account-form').addClass('shake');
            $('#app-title').removeClass('fadeIn');
        <?php endif; ?>
    <?php endforeach; ?>
</script>