<?php

use yii\helpers\Html;

if ($showAtDashboard) {
    print Html::a(Yii::t('SpaceModule.widgets_views_followButton', "Hide at dashboard"), $space->createUrl('/space/membership/switch-dashboard-display', ['show' => 0]), array('data-method' => 'POST', 'class' => 'btn btn-primary'));
} else {
    print Html::a(Yii::t('SpaceModule.widgets_views_followButton', "Show at dashboard"), $space->createUrl('/space/membership/switch-dashboard-display', ['show' => 1]), array('data-method' => 'POST', 'class' => 'btn btn-primary'));
}
