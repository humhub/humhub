<?php

use humhub\components\View;
use humhub\modules\admin\widgets\GroupMemberTable;
use humhub\modules\user\models\Group;
use humhub\widgets\bootstrap\Alert;

/* @var $this View */
/* @var $group Group */
?>

<?php $this->beginContent('@admin/views/group/_manageLayout.php', ['group' => $group]) ?>
<?php if ($subGroupUsersCount = $group->getSubGroupUsersCount()) : ?>
    <?= Alert::accent(Yii::t('AdminModule.user', 'This user group includes {count} additional members from the subgroup(s). To view the subgroups, check the Settings section.', [
            '{count}' => '<strong>' . $subGroupUsersCount . '</strong>',
        ]))->closeButton(false) ?>
<?php endif ?>

<?= GroupMemberTable::widget(['group' => $group]) ?>
<?php $this->endContent(); ?>
