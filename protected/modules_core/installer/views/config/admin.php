<div id="create-admin-account-form" class="panel panel-default animated fadeIn">

    <div class="panel-heading">
        <?php echo Yii::t('InstallerModule.views_config_admin', '<strong>Admin</strong> Account'); ?>
    </div>

    <div class="panel-body">
        <p><?php echo Yii::t('InstallerModule.views_config_admin', "You're almost done. In the last step you have to fill out the form to create an admin account. With this account you can manage the whole network."); ?></p>
        <hr/>
        <?php echo $form; ?>

    </div>
</div>

<script type="text/javascript">

    $(function () {
        // set cursor to email field
        $('#User_username').focus();
    })

    // Shake panel after wrong validation
    <?php foreach($form->models as $model) : ?>
    <?php if ($model->hasErrors()) : ?>
    $('#create-admin-account-form').removeClass('fadeIn');
    $('#create-admin-account-form').addClass('shake');
    <?php endif; ?>
    <?php endforeach; ?>

</script>

