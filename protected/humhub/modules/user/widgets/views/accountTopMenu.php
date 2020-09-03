<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

use humhub\modules\ui\menu\DropdownDivider;
use humhub\widgets\FooterMenu;
use \yii\helpers\Html;
use \yii\helpers\Url;
use humhub\modules\user\widgets\Image;

/* @var $this \humhub\modules\ui\view\components\View */
/* @var $menu \humhub\modules\ui\menu\widgets\DropdownMenu */
/* @var $entries \humhub\modules\ui\menu\MenuEntry[] */
/* @var $options [] */

/** @var \humhub\modules\user\models\User $userModel */

$userModel = Yii::$app->user->identity;

?>

<?php if (Yii::$app->user->isGuest): ?>
    <?php if(!empty($entries)) :?>
        <?= $entries[0]->render() ?>
    <?php endif; ?>
<?php else: ?>
    <?= Html::beginTag('ul', $options) ?>
        <li class="dropdown account">
            <a href="#" id="account-dropdown-link" class="dropdown-toggle" data-toggle="dropdown" aria-label="<?= Yii::t('base', 'Profile dropdown') ?>">

                <?php if ($this->context->showUserName): ?>
                    <div class="user-title pull-left hidden-xs">
                        <strong><?= Html::encode($userModel->displayName); ?></strong><br/><span class="truncate"><?= Html::encode($userModel->displayNameSub); ?></span>
                    </div>
                <?php endif; ?>

                <?= Image::widget([
                        'user' => $userModel,
                        'link'  => false,
                        'width' => 32,
                        'htmlOptions' => [
                                'id' => 'user-account-image',
                 ]])?>

                <b class="caret"></b>
            </a>
            <ul class="dropdown-menu pull-right">
                <?php foreach ($entries as $entry): ?>
                    <?php if(!($entry instanceof DropdownDivider)) : ?><li><?php endif; ?>
                        <?= $entry->render() ?>
                    <?php if(!($entry instanceof DropdownDivider)) : ?></li><?php endif; ?>
                <?php endforeach; ?>
                <?= FooterMenu::widget(['location' => FooterMenu::LOCATION_ACCOUNT_MENU]); ?>
            </ul>
        </li>
    <?= Html::endTag('ul') ?>
<?php endif; ?>
