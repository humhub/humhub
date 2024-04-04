<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

use humhub\modules\ui\menu\DropdownDivider;
use humhub\modules\ui\menu\MenuEntry;
use humhub\modules\ui\menu\widgets\DropdownMenu;
use humhub\modules\ui\view\components\View;
use humhub\modules\user\widgets\Image;
use humhub\widgets\FooterMenu;
use yii\helpers\Html;

/* @var $this View */
/* @var $menu DropdownMenu */
/* @var $entries MenuEntry[] */
/* @var $options [] */

/** @var \humhub\modules\user\models\User $userModel */

$userModel = Yii::$app->user->identity;

?>

<?php if (Yii::$app->user->isGuest): ?>
    <?php if (!empty($entries)) : ?>
        <?= $entries[0]->render() ?>
    <?php endif; ?>
<?php else: ?>
    <?= Html::beginTag('ul', $options) ?>
    <li class="dropdown account">
        <a href="#" id="account-dropdown-link" class="dropdown-toggle" data-toggle="dropdown"
           aria-label="<?= Yii::t('base', 'Profile dropdown') ?>">

            <?php if ($this->context->showUserName): ?>
                <div class="user-title pull-left hidden-xs">
                    <strong><?= Html::encode($userModel->displayName); ?></strong><br/><span
                        class="truncate"><?= Html::encode($userModel->displayNameSub); ?></span>
                </div>
            <?php endif; ?>

            <?= Image::widget([
                'user' => $userModel,
                'link' => false,
                'width' => 32,
                'htmlOptions' => ['id' => 'user-account-image'],
                'showSelfOnlineStatus' => true,
            ]) ?>

            <b class="caret"></b>
        </a>
        <ul class="dropdown-menu pull-right">
            <?php foreach ($entries as $entry): ?>
                <?php if (!($entry instanceof DropdownDivider)) : ?><li><?php endif; ?>
                <?= $entry->render() ?>
                <?php if (!($entry instanceof DropdownDivider)) : ?></li><?php endif; ?>
            <?php endforeach; ?>
            <?= FooterMenu::widget(['location' => FooterMenu::LOCATION_ACCOUNT_MENU]); ?>
        </ul>
    </li>
    <?= Html::endTag('ul') ?>
<?php endif; ?>
