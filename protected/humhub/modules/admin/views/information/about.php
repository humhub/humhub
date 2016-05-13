<?php

use yii\helpers\Html;
?>

<?php if ($isNewVersionAvailable) : ?>
    <div class="alert alert-danger">
        <p>
            <strong><?php echo Yii::t('AdminModule.views_about_index', 'There is a new update available! (Latest version: %version%)', array('%version%' => $latestVersion)); ?></strong><br />
            <?php echo Html::a("http://www.humhub.org", "http://www.humhub.org"); ?>
        </p>
    </div>
<?php elseif ($isUpToDate): ?>
    <div class="alert alert-info">
        <p>
            <strong><?php echo Yii::t('AdminModule.views_about_index', 'This HumHub installation is up to date!'); ?></strong><br />
            <?php echo Html::a("http://www.humhub.org", "http://www.humhub.org"); ?>
        </p>
    </div>
<?php endif; ?>

<p>
    <?php echo Yii::t('AdminModule.views_about_index', 'Currently installed version: %currentVersion%', array('%currentVersion%' => '<strong>' . Yii::$app->version . '</strong>')); ?><br />
</p>
<br />

<?php if (YII_DEBUG) : ?>
    <p class="alert alert-danger">
        <strong><?php echo Yii::t('AdminModule.views_about_index', 'HumHub is currently in debug mode. Disable it when running on production!'); ?></strong><br />
        <?php echo Yii::t('AdminModule.views_about_index', 'See installation manual for more details.'); ?>
    </p>
<?php endif; ?>

<hr />
<span class="pull-right">
    <?php echo Yii::powered(); ?>
</span>
© <?php echo date("Y") ?> HumHub GmbH & Co. KG
&middot;
<?php echo Html::a(Yii::t('AdminModule.views_about_index', 'Licences'), "http://www.humhub.org/licences", array('target' => '_blank')); ?>
