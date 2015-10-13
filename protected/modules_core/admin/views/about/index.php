<div class="panel panel-default">
    <div class="panel-heading"><?php echo Yii::t('AdminModule.views_about_index', '<strong>About</strong> HumHub'); ?></div>
    <div class="panel-body">

        <?php if ($isNewVersionAvailable) : ?>
            <div class="alert alert-danger">
                <p>
                    <strong><?php echo Yii::t('AdminModule.views_about_index', 'There is a new update available! (Latest version: %version%)', array('%version%' => $latestVersion)); ?></strong><br />
                    <?php echo CHtml::link("http://www.humhub.org", "http://www.humhub.org"); ?>
                </p>
            </div>
        <?php elseif ($isUpToDate): ?>
            <div class="alert alert-info">
                <p>
                    <strong><?php echo Yii::t('AdminModule.views_about_index', 'This HumHub installation is up to date!'); ?></strong><br />
                    <?php echo CHtml::link("http://www.humhub.org", "http://www.humhub.org"); ?>
                </p>
            </div>
        <?php endif; ?>

        <p>
            <?php echo Yii::t('AdminModule.views_about_index', 'Currently installed version: %currentVersion%', array('%currentVersion%' => '<strong>' . HVersion::VERSION . '</strong>')); ?><br />
        </p>

        <br />
        <hr />
        <p>
            <span class="pull-right">
                <?php echo Yii::powered(); ?>
            </span>
            © <?php echo date("Y") ?> <a href="http://www.humhub.com">HumHub GmbH & Co. KG</a>
            &middot;
            <?php echo CHtml::link(Yii::t('AdminModule.views_about_index', 'Licences'), "http://www.humhub.com/licences", array('target' => '_blank')); ?>
            <br />

        </p>

    </div>
</div>
