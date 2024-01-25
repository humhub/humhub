<?php

use humhub\modules\admin\widgets\PendingRegistrations;

/** @var $searchModel \humhub\modules\admin\models\PendingRegistrationSearch */
/** @var $dataProvider yii\data\ActiveDataProvider */
/** @var $types array */


?>

<div class="card-body">
    <?=
    PendingRegistrations::widget([
        'dataProvider' => $dataProvider,
        'searchModel' => $searchModel,
        'types' => $types,
    ]);
    ?>
</div>
