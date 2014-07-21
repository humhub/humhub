<?php
// set tour status to seen for current user
Yii::app()->user->getModel()->setSetting("seen", "true", "tour");
?>