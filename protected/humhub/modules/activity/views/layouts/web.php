<?php

use humhub\modules\content\components\ContentContainerController;
use humhub\modules\space\models\Space;
use humhub\modules\space\widgets\Image;
use humhub\widgets\TimeAgo;
use yii\helpers\Url;

/* @var $user \humhub\modules\user\models\User */
/* @var $url string */
/* @var $content \humhub\modules\content\models\Content */
/* @var $contentAddon \humhub\modules\content\interfaces\ContentProvider */
/* @var $contentContainer \humhub\modules\content\models\ContentContainer */
/* @var $createdAt string */
/* @var $message string */

?>

<?php if (!empty($url)) : ?>
<a href="<?= Url::to($url) ?>">
    <?php endif; ?>

    <div class="d-flex activity-box-entry">
        <div class="flex-shrink-0 me-3 pt-1 img-profile-space">
            <?= $user->getProfileImage()->render(32, ['link' => false]) ?>

            <!-- Show space image, if you are outside from a space -->
            <?php if (!Yii::$app->controller instanceof ContentContainerController && $contentContainer->polymorphicRelation instanceof Space) : ?>
                <?= Image::widget([
                    'space' => $contentContainer->polymorphicRelation,
                    'width' => 20,
                    'htmlOptions' => ['class' => 'img-space'],
                ]) ?>
            <?php endif; ?>
        </div>

        <div class="flex-grow-1 text-break">
            <?= $message ?>
            <br>
            <?= TimeAgo::widget(['timestamp' => $createdAt]) ?>
        </div>
    </div>

    <?php if (!empty($url)) : ?>
</a>
<?php endif; ?>
