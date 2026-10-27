<?php

use humhub\components\View;
use humhub\modules\admin\widgets\AdvancedSettingMenu;
use humhub\modules\admin\widgets\SettingsMenu;
use humhub\widgets\ContentCard;

/* @var View $this */
/* @var string $content */

SettingsMenu::markAsActive(['/admin/setting/advanced']);
?>
<?php ContentCard::begin([
    'title' => Yii::t('AdminModule.base', 'Advanced'),
    'tabs' => AdvancedSettingMenu::class,
]) ?>
<?= $content ?>
<?php ContentCard::end() ?>
