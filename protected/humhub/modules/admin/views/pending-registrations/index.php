<?php

use humhub\modules\admin\models\PendingRegistrationSearch;
use humhub\modules\admin\widgets\PendingRegistrations;

/** @var $searchModel PendingRegistrationSearch */
/** @var $dataProvider yii\data\ActiveDataProvider */
/** @var $types array */


?>

<?= PendingRegistrations::widget([
    'dataProvider' => $dataProvider,
    'searchModel' => $searchModel,
    'types' => $types,
]) ?>
