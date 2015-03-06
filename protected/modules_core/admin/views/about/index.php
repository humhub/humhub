<div class="panel panel-default">
    <div class="panel-heading"><?php echo Yii::t('AdminModule.views_about_index', '<strong>About</strong> HumHub'); ?></div>
    <div class="panel-body">
        <p>Version: <?php echo HVersion::VERSION; ?></p>
        <p><?php echo Yii::powered(); ?></p>
        
        <div class="alert alert-info">

            © 2010 - <?php echo date("Y") ?> The HumHub Project

        </div>

    </div>
</div>
