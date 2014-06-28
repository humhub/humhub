<div class="panel panel-default">
    <div class="panel-body">
        <p class="lead"><?php echo Yii::t('InstallerModule.base', "<strong>Congratulations</strong>. You're done."); ?></p>

        <p>The installation completed successfully! Have fun with your new social network.</p>

        <hr>
        <?php echo HHtml::link(Yii::t('InstallerModule.base', 'Sign in'), Yii::app()->createUrl('/site/index'), array('class' => 'btn btn-primary')); ?>
    </div>
</div>
