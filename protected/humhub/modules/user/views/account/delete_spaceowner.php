<?php

use humhub\helpers\Html;
use humhub\modules\space\widgets\Image;

?>
<?php $this->beginContent('@user/views/account/_userProfileLayout.php') ?>
<strong><?= Yii::t('UserModule.account', 'Your account cannot be deleted!'); ?></strong><br/>
<br/>
<?= Yii::t('UserModule.account', 'You are currently the owner of following spaces:'); ?><br/>

<?php foreach ($ownSpaces as $space): ?>
    <div class="d-flex">
        <div class="flex-shrink-0 me-2">
            <?= Image::widget(['space' => $space, 'width' => 38, 'link' => true]); ?>
        </div>
        <div class="flex-grow-1">
            <h4 class="mt-0"><?= Html::containerLink($space); ?></h4>
            <?= Yii::t('SpaceModule.base', '{count} members', ['count' => $space->getMemberships()->count()]); ?>
        </div>
    </div>
<?php endforeach; ?>
<br/>

<strong><?= Yii::t('UserModule.account', 'You must transfer ownership or delete these spaces before you can delete your account.'); ?></strong>
<br/>
<br/>
<?php $this->endContent(); ?>
