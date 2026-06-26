<?php

use humhub\assets\CardsAsset;
use humhub\helpers\Html;
use humhub\modules\user\components\PeopleQuery;
use humhub\modules\user\widgets\PeopleCard;
use humhub\modules\user\widgets\PeopleFilters;
use humhub\modules\user\widgets\PeopleHeadingButtons;
use yii\web\View;

/* @var $this View */
/* @var $people PeopleQuery */

CardsAsset::register($this);
?>
<div class="panel panel-default">

    <div class="panel-heading">
        <?= Yii::t('UserModule.base', '<strong>People</strong>') ?>
        <?= PeopleHeadingButtons::widget() ?>
    </div>

    <div class="panel-body">
        <?= PeopleFilters::widget(['query' => $people]) ?>
    </div>

</div>

<div class="row cards" aria-live="polite" aria-atomic="false">
    <?php if (!$people->exists()): ?>
        <div class="col-lg-12">
            <div class="panel panel-default">
                <div class="panel-body">
                    <p role="status" aria-live="polite" class="m-0">
                        <strong><?= Yii::t('UserModule.base', 'No results found!') ?></strong><br>
                        <?= Yii::t('UserModule.base', 'Try other keywords or remove filters.') ?>
                    </p>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <?php foreach ($people->all() as $user) : ?>
        <?= PeopleCard::widget(['user' => $user]); ?>
    <?php endforeach; ?>
</div>

<?php if (!$people->isLastPage()) : ?>
    <?= Html::tag('div', '', [
        'class' => 'cards-end',
        'role' => 'status',
        'aria-label' => Yii::t('UserModule.base', 'Loading more people'),
        'aria-live' => 'polite',
        'data-current-page' => $people->pagination->getPage() + 1,
        'data-total-pages' => $people->pagination->getPageCount(),
        'data-ui-loader' => '',
    ]) ?>
<?php endif; ?>
