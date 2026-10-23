<?php

use humhub\helpers\ControllerHelper;
use yii\helpers\Url;

$tabs = [
    ['label' => Yii::t('OauthModule.base', 'Settings'), 'route' => '/oauth/admin/index', 'actions' => ['index']],
    ['label' => Yii::t('OauthModule.base', 'Clients'), 'route' => '/oauth/admin/clients', 'actions' => ['clients', 'client', 'regenerate-secret']],
    ['label' => Yii::t('OauthModule.base', 'Grants'), 'route' => '/oauth/admin/grants', 'actions' => ['grants']],
];
?>
<ul class="nav nav-tabs tab-sub-menu">
    <?php foreach ($tabs as $tab): ?>
        <li class="nav-item">
            <a class="nav-link<?= ControllerHelper::isActivePath('oauth', 'admin', $tab['actions']) ? ' active' : '' ?>"
               href="<?= Url::to([$tab['route']]) ?>"><?= $tab['label'] ?></a>
        </li>
    <?php endforeach; ?>
</ul>
