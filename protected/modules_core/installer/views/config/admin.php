<div id="create-admin-account-form" class="panel panel-default animated fadeIn">

    <div class="install-header install-header-small" style="background-image: url('<?php echo $this->module->assetsUrl; ?>/humhub-install-header.jpg');">
        <h2 class="install-header-title"><?php echo Yii::t('InstallerModule.base', '<strong>Admin</strong> Account'); ?></h2>
    </div>

    <div class="panel-body">
        <p><?php echo Yii::t('InstallerModule.base', "You're almost done. In the last step you have to fill out the form to create an admin account. With this account you can manage the whole network."); ?></p>
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

