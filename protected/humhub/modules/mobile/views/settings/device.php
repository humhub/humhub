<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */


\humhub\modules\mobile\assets\MobileAsset::register($this);

?>

<div class="panel-heading">
    <?= Yii::t('MobileModule.settings', '<strong>Device</strong> settings'); ?>
</div>

<div class="panel-body">
    <?= \humhub\widgets\Button::primary('Close')->action('mobile.close') ?>

    <div class="table-responsive">
        <table class="table">
            <tr>
                <th>Key</th>
                <th>Value</th>
            </tr>
            <?php foreach (Yii::$app->request->headers as $key => $value) : ?>
                <tr>
                    <td><?= $key ?></td>
                    <td><?= $value[0] ?></td>
                </tr>
            <?php endforeach ?>
        </table>
    </div>
</div>