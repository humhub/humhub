<?php

use humhub\modules\admin\widgets\AdminMenu;
use humhub\modules\admin\widgets\UserMenu;
use humhub\widgets\PageHeader;

/* @var $content string */

// Also on the pages modules add to this section
AdminMenu::markAsActive('user');
?>
<?php $this->beginContent('@admin/views/layouts/main.php') ?>
<div class="d-flex flex-column gap-3">
    <?= PageHeader::title(Yii::t('AdminModule.base', 'Users'))->tabs(UserMenu::class) ?>
    <?php if (!empty($this->params['adminContentCard'])): ?>
        <?= $content ?>
    <?php else: ?>
        <div class="panel panel-default">
            <?= $content ?>
        </div>
    <?php endif; ?>
</div>
<?php $this->endContent() ?>
