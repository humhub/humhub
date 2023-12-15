<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

use humhub\libs\Html;
use humhub\modules\admin\controllers\InformationController;
use humhub\modules\ui\view\components\View;

/**
 * @var $this View
 * @var $databaseName string
 * @var $migrationStatus int
 * @var $migrationOutput string
 * @var $rebuildSearchRunning boolean
 */

?>
<div>
    <p>
        <?php if ($rebuildSearchRunning): ?>
            <div class="alert alert-info"><?= Yii::t('AdminModule.information', 'Search index rebuild in progress.'); ?></div>
        <?php else: ?>
            <?= Html::a('Rebuild search index', ['/admin/information/database', 'rebuildSearch' => 1], ['class' => 'btn btn-primary pull-right', 'data-method' => 'post', 'data-ui-loader' => '']); ?>
        <?php endif; ?>

        <?= Yii::t('AdminModule.information', 'The current main HumHub database name is ') ?>
        <i><b><?= Html::encode($databaseName) ?></b></i>
    </p>
</div>

<div>
<?php if ($migrationStatus === InformationController::DB_ACTION_PENDING): ?>
    <p><?= Yii::t('AdminModule.information', 'Outstanding database migrations:'); ?></p>
    <div class="well">
    <pre>
        <?= $migrationOutput ?>
    </pre>
    </div>
    <p><br>
        <?= Html::a(
            Yii::t('AdminModule.information', 'Refresh'),
            ['/admin/information/database'],
            [
                'id' => 'migrationRun',
                'class' => 'btn btn-primary pull-right',
            ]
        ); ?>
    </p>
<?php elseif ($migrationStatus === InformationController::DB_ACTION_RUN): ?>
    <p><?= Yii::t('AdminModule.information', 'Database migration results:'); ?></p>
    <div class="well">
    <pre>
        <?= $migrationOutput ?>
    </pre>
    </div>
    <p><br>
        <?= Html::a(
            Yii::t('AdminModule.information', 'Update Database'),
            ['/admin/information/database', 'migrate' => 1],
            [
                'id' => 'migrationRun',
                'class' => 'btn btn-primary pull-right',
            ]
        ); ?>
    </p>
<?php else: ?>
    <p><?= Yii::t('AdminModule.information', 'Your database is <b>up-to-date</b>.'); ?></p>
<?php endif; ?>
</div>
