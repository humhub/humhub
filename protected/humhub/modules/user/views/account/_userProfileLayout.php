<?php

use humhub\components\View;
use humhub\modules\user\widgets\AccountProfileMenu;
use humhub\widgets\PageHeader;

/* @var View $this */
/* @var string $content */

$this->params['accountPanel'] = false;
?>
<div class="d-flex flex-column gap-3">
    <?= PageHeader::title(Yii::t('UserModule.account', 'Profile'))->tabs(AccountProfileMenu::class) ?>
    <div class="panel panel-default">
        <div class="panel-body">
            <?= $content ?>
        </div>
    </div>
</div>
