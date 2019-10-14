<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

use humhub\widgets\FooterMenu;
use \yii\helpers\Html;
use \yii\helpers\Url;

/** @var \humhub\modules\user\models\User $userModel */
$userModel = Yii::$app->user->getIdentity();
?>
<?php if ($userModel === null): ?>
    <a href="#" class="btn btn-enter" data-action-click="ui.modal.load" data-action-url="<?= Url::toRoute('/user/auth/login'); ?>">
        <?php if (Yii::$app->getModule('user')->settings->get('auth.anonymousRegistration')): ?>
            <?= Yii::t('UserModule.base', 'Sign in / up'); ?>
        <?php else: ?>
            <?= Yii::t('UserModule.base', 'Sign in'); ?>
        <?php endif; ?>
    </a>
<?php else: ?>
    <ul class="nav">
        <li class="dropdown account">
            <a href="#" id="account-dropdown-link" class="dropdown-toggle" data-toggle="dropdown" aria-label="<?= Yii::t('base', 'Profile dropdown') ?>">

                <img id="user-account-image" class="img-rounded"
                     src="<?= $userModel->getProfileImage()->getUrl(); ?>"
                     height="38" width="38" alt="<?= Yii::t('base', 'My profile image') ?>" data-src="holder.js/38x38"
                     style="width: 38px; height: 38px;"/>
                
            </a>
            <ul class="dropdown-menu pull-right">
                <?php foreach ($this->context->getItems() as $item): ?>
                    <?php if ($item['label'] == '---'): ?>
                        <li class="divider"></li>
                        <?php else: ?>
                        <li>
                            <a <?= isset($item['id']) ? 'id="' . $item['id'] . '"' : '' ?> href="<?= $item['url']; ?>" <?= isset($item['pjax']) && $item['pjax'] === false ? 'data-pjax-prevent' : '' ?>>
                                <?= $item['icon'] . ' ' . $item['label']; ?>
                            </a>
                        </li>
                    <?php endif; ?>
                <?php endforeach; ?>
                <?= FooterMenu::widget(['location' => FooterMenu::LOCATION_ACCOUNT_MENU]); ?>
            </ul>
        </li>
    </ul>
<?php endif; ?>
