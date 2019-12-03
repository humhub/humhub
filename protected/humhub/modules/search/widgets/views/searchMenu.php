<?php

use yii\helpers\Url;
use humhub\libs\Html;
?>

<li class="dropdown search-menu">
    <a href="<?= Url::to(['/search/search/index']); ?>" id="search-menu" class="dropdown-toggle" aria-label="<?= Yii::t('SearchModule.base', 'Search for user, spaces and content') ?>">
        <i class="fa fa-search"></i>
    </a>
</li>

<script <?= Html::nonce() ?>>
    /**
     * Open search menu
     */
    $('#search-menu-nav').click(function () {

        // use setIntervall to setting the focus
        var searchFocus = setInterval(setFocus, 10);

        function setFocus() {

            // set focus
            $('#search-menu-search').focus();
            // stop interval
            clearInterval(searchFocus);
        }

    })
</script>
