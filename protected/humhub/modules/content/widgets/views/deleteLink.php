<?php

use yii\helpers\Url;

/* @var $this humhub\modules\ui\view\components\View */
?>
<li>
    <!-- load modal confirm widget -->
    <a  href="#" data-action-click="delete">
           <i class="fa fa-trash-o"></i> <?= Yii::t('ContentModule.base', 'Delete') ?>
    </a>
</li>
