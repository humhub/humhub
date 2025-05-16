<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

use humhub\components\View;
use humhub\helpers\Html;
use humhub\services\MigrationService;
use humhub\widgets\bootstrap\Button;

/**
 * @var $this View
 * @var $databaseName string
 * @var $migrationStatus int
 * @var $migrationOutput string
 * @var $rebuildSearchRunning bool
 */

?>
<div>
    <?php if ($rebuildSearchRunning): ?>
        <div class="alert alert-info"><?= Yii::t('AdminModule.information', 'Search index rebuild in progress.'); ?></div>
    <?php else: ?>
        <div class="clearfix">
            <?= Button::primary(Yii::t('AdminModule.information', 'Rebuild search index'))
            ->link(['/admin/information/database', 'rebuildSearch' => 1])
            ->options(['data-method' => 'post'])
            ->right() ?>
        </div>
    <?php endif; ?>

    <?= Yii::t('AdminModule.information', 'The current main HumHub database name is ') ?>
    <i><b><?= Html::encode($databaseName) ?></b></i>
</div>

<div>
    <?php if ($migrationStatus === MigrationService::DB_ACTION_PENDING): ?>
        <p><?= Yii::t('AdminModule.information', 'Outstanding database migrations:'); ?></p>
        <div class="bg-light p-3">
    <pre>
        <?= $migrationOutput ?>
    </pre>
        </div>
        <br>
        <p class="clearfix">
            <?= Button::primary(Yii::t('AdminModule.information', 'Update Database'))
                ->link(['/admin/information/database', 'migrate' => 1])
                ->right() ?>
        </p>
    <?php elseif ($migrationStatus === MigrationService::DB_ACTION_RUN): ?>
        <p><?= Yii::t('AdminModule.information', 'Database migration results:'); ?></p>
        <div class="bg-light p-3">
    <pre>
        <?= $migrationOutput ?>
    </pre>
        </div>
        <br>
        <p class="clearfix">
            <?= Button::primary(Yii::t('AdminModule.information', 'Refresh'))
                ->link(['/admin/information/database'])
                ->right() ?>
        </p>
    <?php else: ?>
        <p><?= Yii::t('AdminModule.information', 'Your database is <b>up-to-date</b>.'); ?></p>
    <?php endif; ?>
</div>
