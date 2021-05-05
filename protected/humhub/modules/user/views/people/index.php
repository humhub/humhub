<?php
use humhub\libs\Html;
use humhub\modules\user\assets\PeopleAsset;
use humhub\modules\user\components\PeopleQuery;
use humhub\modules\user\widgets\PeopleCard;
use humhub\modules\user\widgets\PeopleFilters;
use humhub\widgets\LinkPager;
use humhub\widgets\ModalButton;

/* @var $this \yii\web\View */
/* @var $people PeopleQuery */
/* @var $showInviteButton bool */

PeopleAsset::register($this);
?>
<div class="panel panel-default">

    <div class="panel-heading">
        <?php if ($people->isFilteredByGroup()) : ?>
            <?= Yii::t('UserModule.base', '<strong>Group</strong> members - {group}', ['{group}' => Html::encode($people->filteredGroup->name)]); ?>
        <?php else: ?>
            <?= Yii::t('UserModule.base', '<strong>Member</strong> directory'); ?>
        <?php endif; ?>

        <?php if ($showInviteButton): ?>
            <?= ModalButton::primary(Yii::t('UserModule.base', 'Send invite'))
                ->load(['/user/invite'])->icon('invite')->sm()->right() ?>
        <?php endif; ?>
    </div>

    <div class="panel-body">
        <?= PeopleFilters::widget(); ?>
    </div>

</div>

<div class="row<?php if (PeopleCard::hasBothSides()) : ?> cards-rotatable<?php endif; ?>">
    <?php if (!$people->exists()): ?>
    <div class="col-md-12">
        <div class="panel panel-default">
            <div class="panel-body">
                <?= Yii::t('UserModule.base', 'No people found!'); ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <?php foreach ($people->all() as $user) : ?>
    <div class="card col-lg-3 col-md-4 col-sm-6 col-xs-12">
        <div class="card-people">
            <?= PeopleCard::widget(['user' => $user]); ?>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<div class="pagination-container">
    <?= LinkPager::widget(['pagination' => $people->pagination]); ?>
</div>
