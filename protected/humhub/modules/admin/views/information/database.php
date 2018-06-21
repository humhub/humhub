<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

/**
 * @var $this \humhub\components\View
 * @var $databaseName string
 * @var $migrate string
 */
?>
<div>
    <p>
        <?= \Yii::t('base', 'The current main HumHub database name is ') ?>
        <i><b><?= \yii\helpers\Html::encode($databaseName) ?></b></i>
    </p>
</div>

<p>Database migration results:</p>

<div class="well">
    <pre>
        <?= $migrate; ?>
    </pre>
</div>
