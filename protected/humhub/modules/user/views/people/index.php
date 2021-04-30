<?php
use humhub\libs\Html;
use humhub\modules\user\assets\PeopleAsset;
use humhub\modules\user\widgets\PeopleCard;
use humhub\widgets\LinkPager;
use humhub\widgets\ModalButton;
use yii\helpers\Url;

/* @var $this \yii\web\View */
/* @var $keyword string */
/* @var $group humhub\modules\user\models\Group */
/* @var $users humhub\modules\user\models\User[] */
/* @var $pagination yii\data\Pagination */
/* @var $showInviteButton bool */

PeopleAsset::register($this);
?>
<div class="panel panel-default">

    <div class="panel-heading">
        <?php if ($group === null) : ?>
            <?= Yii::t('UserModule.base', '<strong>Member</strong> directory'); ?>
        <?php else: ?>
            <?= Yii::t('UserModule.base', '<strong>Group</strong> members - {group}', ['{group}' => Html::encode($group->name)]); ?>
        <?php endif; ?>

        <?php if ($showInviteButton): ?>
            <?= ModalButton::primary(Yii::t('UserModule.base', 'Send invite'))
                ->load(['/user/invite'])->icon('invite')->sm()->right() ?>
        <?php endif; ?>
    </div>

    <div class="panel-body">
        <?= Html::beginForm('', 'get', ['class' => 'form-search']); ?>
        <div class="row">
            <div class="col-md-6">
                <div class="form-group form-group-search">
                    <div class="form-search-field-info"><?= Yii::t('UserModule.base', 'Free text search in the directory (name, first name, telephone number, etc.)') ?></div>
                    <?= Html::hiddenInput('page', '1'); ?>
                    <?= Html::textInput("keyword", $keyword, ['class' => 'form-control form-search', 'placeholder' => Yii::t('UserModule.base', 'search for members')]); ?>
                    <?= Html::submitButton('<span class="fa fa-search"></span>', ['class' => 'form-button-search']); ?>
                </div>
            </div>
            <div class="col-md-6">
                <?= Html::a(Yii::t('UserModule.base', 'Reset filter'), Url::to(['/user/people']), ['class' => 'form-search-reset']); ?>
            </div>
        </div>
        <?= Html::endForm(); ?>
    </div>

</div>

<div class="row">
    <?php if (count($users) == 0): ?>
    <div class="col-md-12">
        <div class="panel panel-default">
            <div class="panel-body">
                <?= Yii::t('UserModule.base', 'No people found!'); ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <?php foreach ($users as $user) : ?>
    <div class="card col-lg-3 col-md-4 col-sm-6 col-xs-12">
        <div class="card-people">
            <?= PeopleCard::widget(['user' => $user, 'side' => 'front']); ?>
            <?= PeopleCard::widget(['user' => $user, 'side' => 'back']); ?>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<div class="pagination-container">
    <?= LinkPager::widget(['pagination' => $pagination]); ?>
</div>
