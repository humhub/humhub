<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

use humhub\libs\Html;
use humhub\modules\ui\view\components\View;
use humhub\services\MigrationService;
use humhub\widgets\Button;

/**
 * @var $this View
 * @var $databaseName string
 * @var $migrationStatus int
 * @var $migrationOutput string
 * @var $rebuildSearchRunning bool
 */

?>
<div>
    <p>
        <?php if ($rebuildSearchRunning): ?>
    <div class="alert alert-info"><?= Yii::t('AdminModule.information', 'Search index rebuild in progress.'); ?></div>
    <?php else: ?>
        <?= Button::primary(Yii::t('AdminModule.information', 'Rebuild search index'))
            ->link(['/admin/information/database', 'rebuildSearch' => 1])
            ->options(['data-method' => 'post'])
            ->right() ?>
    <?php endif; ?>

    <?= Yii::t('AdminModule.information', 'The current main HumHub database name is ') ?>
    <i><b><?= Html::encode($databaseName) ?></b></i>
    </p>
</div>

<div>
    <?php if ($migrationStatus === MigrationService::DB_ACTION_PENDING): ?>
        <p><?= Yii::t('AdminModule.information', 'Outstanding database migrations:'); ?></p>
        <div class="well">
    <pre>
        <?= $migrationOutput ?>
    </pre>
        </div>
        <p><br>
            <?= Button::primary(Yii::t('AdminModule.information', 'Update Database'))
                ->link(['/admin/information/database', 'migrate' => 1])
                ->right() ?>
        </p>
    <?php elseif ($migrationStatus === MigrationService::DB_ACTION_RUN): ?>
        <p><?= Yii::t('AdminModule.information', 'Database migration results:'); ?></p>
        <div class="well">
    <pre>
        <?= $migrationOutput ?>
    </pre>
        </div>
        <p><br>
            <?= Button::primary(Yii::t('AdminModule.information', 'Refresh'))
                ->link(['/admin/information/database'])
                ->right() ?>
        </p>
    <?php else: ?>
        <p><?= Yii::t('AdminModule.information', 'Your database is <b>up-to-date</b>.'); ?></p>
    <?php endif; ?>
</div>
