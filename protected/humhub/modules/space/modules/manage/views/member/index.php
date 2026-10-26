<?php

use humhub\components\View;
use humhub\modules\space\models\Space;
use humhub\modules\space\modules\manage\widgets\MemberMenu;
use humhub\modules\space\modules\manage\widgets\SpaceMemberTable;

/* @var $this View */
/* @var $space Space */
?>
<?php $this->beginContent('@space/modules/manage/views/layouts/section.php', [
    'title' => Yii::t('SpaceModule.manage', 'Members'),
    'tabs' => new MemberMenu(['space' => $space]),
]) ?>
<?= SpaceMemberTable::widget(['space' => $space]) ?>
<?php $this->endContent() ?>
