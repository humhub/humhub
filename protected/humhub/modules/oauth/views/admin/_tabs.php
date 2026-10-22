<?php

use humhub\helpers\ControllerHelper;
use yii\helpers\Url;

$tabs = [
    ['label' => Yii::t('OauthModule.base', 'Settings'), 'route' => '/oauth/admin/index', 'actions' => ['index']],
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
