<div class="panel panel-default">
    <div class="panel-body">

        <p class="lead"><?php echo Yii::t('InstallerModule.base', '<strong>Welcome</strong> to the HumHub installer.'); ?></p>

        <p><?php echo Yii::t('InstallerModule.base', 'The HumHub basic installation needs just a web server who supports <strong>PHP 5.3</strong> or higher and <strong>MySQL 5.4</strong> or higher.'); ?></p>
        <p><?php echo Yii::t('InstallerModule.base', 'For installing HumHub to your MySQL database you have to know the following database information before proceeding:'); ?></p>
        <ol>
            <li><?php echo Yii::t('InstallerModule.base', 'Hostname'); ?></li>
            <li><?php echo Yii::t('InstallerModule.base', 'Username'); ?></li>
            <li><?php echo Yii::t('InstallerModule.base', 'Password'); ?></li>
            <li><?php echo Yii::t('InstallerModule.base', 'Name of database'); ?></li>
        </ol>
        <p><?php echo Yii::t('InstallerModule.base', 'In the next step HumHub will check your system to determine if your configurations are comparable with the needed requirements.'); ?></p>

        <hr>

        <?php echo HHtml::link(Yii::t('InstallerModule.base',"Ok, let's go"). ' <i class="fa fa-arrow-circle-right"></i>', array('go'), array('class' => 'btn btn-lg btn-primary')); ?>
    </div>


</div>
</div>
<?php echo Yii::t('InstallerModule.base', ''); ?>