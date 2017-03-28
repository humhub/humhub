<?php

use yii\helpers\Html;
?>

<?php if ($isNewVersionAvailable) : ?>
    <div class="alert alert-danger">
        <p>
            <strong><?= Yii::t('AdminModule.views_about_index', 'There is a new update available! (Latest version: %version%)', ['%version%' => $latestVersion]); ?></strong><br>
            <?= Html::a("https://www.humhub.org", "https://www.humhub.org"); ?>
        </p>
    </div>
<?php elseif ($isUpToDate): ?>
    <div class="alert alert-info">
        <p>
            <strong><?= Yii::t('AdminModule.views_about_index', 'This HumHub installation is up to date!'); ?></strong><br />
            <?= Html::a("https://www.humhub.org", "https://www.humhub.org"); ?>
        </p>
    </div>
<?php endif; ?>

<p>
    <?= Yii::t('AdminModule.views_about_index', 'Currently installed version: %currentVersion%', ['%currentVersion%' => '<strong>' . Yii::$app->version . '</strong>']); ?><br>
</p>
<br>

<?php if (YII_DEBUG) : ?>
    <p class="alert alert-danger">
        <strong><?= Yii::t('AdminModule.views_about_index', 'HumHub is currently in debug mode. Disable it when running on production!'); ?></strong><br>
        <?= Yii::t('AdminModule.views_about_index', 'See installation manual for more details.'); ?>
    </p>
<?php endif; ?>

<hr>
<span class="pull-right">
    <?= Yii::powered(); ?>
</span>
© <?= date("Y") ?> HumHub GmbH & Co. KG
&middot;
<?= Html::a(Yii::t('AdminModule.views_about_index', 'Licences'), "https://www.humhub.org/licences", ['target' => '_blank']); ?>
