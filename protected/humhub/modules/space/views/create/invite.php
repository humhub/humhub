<?php

use humhub\modules\space\models\forms\InviteForm;
use humhub\modules\space\models\Space;
use humhub\modules\space\widgets\InviteModal;
use yii\helpers\Url;

/**
 * @var $space Space
 * @var $model InviteForm
 */
?>

<?= InviteModal::widget([
    'model' => $model,
    'submitText' => Yii::t('SpaceModule.base', 'Done'),
    'submitAction' => Url::to(['/space/create/invite', 'spaceId' => $space->id])
]); ?>
