<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2020 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

use humhub\libs\Html;
use yii\helpers\Url;

/**
 * @var integer groupId
 */

?>

<div class="panel-body">
    <div class="alert alert-info"><?= Yii::t('AdminModule.information', 'Search index rebuild in progress.'); ?></div>
    <div class="reassign-spaces">
        <?= Html::a(Yii::t('AdminModule.modules', 'Reassign All'), Url::to(['/admin/group/reassign-all', 'id' => $groupId]), ['class' => 'btn btn-primary btn-reassign', 'data-method' => 'POST', 'data-confirm' => Yii::t('AdminModule.modules', 'Are you sure? Reassign default spaces to all users?')]); ?>
    </div>
</div>

