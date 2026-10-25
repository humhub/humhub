<?php

use humhub\components\View;
use humhub\modules\friendship\widgets\ManageMenu;
use humhub\modules\user\models\User;
use humhub\widgets\PageHeader;

/* @var View $this */
/* @var User $user */
/* @var string $content */

$this->params['accountPanel'] = false;
?>
<div class="d-flex flex-column gap-3">
    <?= PageHeader::title(Yii::t('FriendshipModule.base', 'Friends'))->tabs(new ManageMenu(['user' => $user])) ?>
    <div class="panel panel-default">
        <div class="panel-body">
            <?= $content ?>
        </div>
    </div>
</div>
