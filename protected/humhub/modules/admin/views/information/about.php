<?php

use humhub\helpers\Html;
use humhub\modules\marketplace\widgets\AboutVersion;

?>

<div class="alert alert-secondary">
    <?= AboutVersion::widget() ?>
</div>
<br/>

<?php if ($isNewVersionAvailable) : ?>
    <div class="alert alert-danger">
        <strong><?= Yii::t('AdminModule.information', 'There is a new update available! (Latest version: %version%)', ['%version%' => $latestVersion]); ?></strong><br>
        <?= Html::a("https://www.humhub.org", "https://www.humhub.org"); ?>
    </div>
<?php elseif ($isUpToDate): ?>
    <div class="alert alert-info">
        <strong><?= Yii::t('AdminModule.information', 'This HumHub installation is up to date!'); ?></strong><br/>
        <?= Html::a("https://www.humhub.org", "https://www.humhub.org"); ?>
    </div>
<?php endif; ?>

<br>

<?php if (YII_DEBUG) : ?>
    <p class="alert alert-danger">
        <strong><?= Yii::t('AdminModule.information', 'HumHub is currently in debug mode. Disable it when running on production!'); ?></strong><br>
        <?= Yii::t('AdminModule.information', 'See installation manual for more details.'); ?>
    </p>
<?php endif; ?>

<hr>
© <?= date("Y") ?> HumHub GmbH & Co. KG
&middot;
<?= Html::a(Yii::t('AdminModule.information', 'Licences'), "https://www.humhub.org/licences", ['target' => '_blank']); ?>
