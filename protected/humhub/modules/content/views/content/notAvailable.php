<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2023 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

use humhub\modules\content\components\ContentContainerActiveRecord;
use humhub\modules\space\models\Space;
use humhub\modules\user\models\User;

/* @var ContentContainerActiveRecord $container */
?>
<div class="panel panel-default">
    <div class="panel-body">
        <div class="alert alert-danger" style="margin:0">
            <h4 style="margin-top:0;font-weight:500"><?= Yii::t('ContentModule.base', 'Access denied') ?></h4>
            <p class="text-muted">
                <?php if ($container instanceof Space) : ?>
                    <?= Yii::t('ContentModule.base', 'You do not have permission to access this content, as it is reserved for members of this Space. Please become a member or apply for membership. The available options for membership will depend on the Space\'s settings.') ?>
                <?php elseif ($container instanceof User) : ?>
                    <?= Yii::t('ContentModule.base', 'You do not have permission to access this content. The user has marked it as private.') ?>
                <?php endif; ?>
            </p>
        </div>
    </div>
</div>