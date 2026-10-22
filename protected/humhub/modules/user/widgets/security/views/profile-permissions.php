<?php

use humhub\helpers\Html;
use humhub\modules\user\models\User;
use humhub\modules\user\widgets\PermisionGridModuleFilter;
use humhub\modules\user\widgets\PermissionGridEditor;
use yii\helpers\Url;

/* @var User $user */
/* @var string[] $groups */
/* @var string $group */
?>
<div class="clearfix">
    <div class="float-end me-2 mb-2">
        <?= PermisionGridModuleFilter::widget() ?>
    </div>
</div>

<?php if (count($groups) > 1) : ?>
    <div class="tab-menu permission-group-tabs">
        <ul class="nav nav-tabs" role="tablist">
            <?php foreach ($groups as $groupId => $groupTitle) : ?>
                <li role="presentation" class="nav-item">
                    <a class="nav-link<?= ($groupId == $group) ? ' active' : '' ?>" href="<?= Url::to(['/user/security/privacy', 'groupId' => $groupId]) ?>"
                       role="tab"><?= Html::encode($groupTitle) ?></a>
                </li>
            <?php endforeach ?>
        </ul>
    </div>
    <br/>
<?php endif ?>

<div class="tab-content">
    <?= PermissionGridEditor::widget(['permissionManager' => $user->permissionManager, 'groupId' => $group]) ?>
</div>
