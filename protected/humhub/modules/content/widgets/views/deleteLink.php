<?php

use yii\helpers\Url;

/* @var $this humhub\components\View */
?>
<li>
    <!-- load modal confirm widget -->
    <a  href="#" data-action-click="delete">
           <i class="fa fa-trash-o"></i> <?= Yii::t('ContentModule.widgets_views_deleteLink', 'Delete') ?> 
    </a>
</li>