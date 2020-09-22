<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

use humhub\components\ActiveRecord;
use humhub\modules\content\components\ContentContainerController;
use humhub\modules\space\models\Space;
use humhub\modules\space\widgets\Image;
use humhub\widgets\TimeAgo;
use yii\helpers\Url;

/* @var $originator \humhub\modules\user\models\User  */
/* @var $clickable boolean  */
/* @var $record ActiveRecord  */

?>

<?php if ($clickable) : ?>
<a href="<?= Url::to(['/activity/link', 'id' => $record->id])?>">
<?php endif; ?>
    <li class="activity-entry" data-stream-entry data-action-component="activity.ActivityStreamEntry" data-content-key="<?= $record->content->id ?>">
        <div class="media">
            <?php if ($originator !== null) : ?>
                <!-- Show user image -->
                <?= $originator->getProfileImage()->render(32, ['class' => 'media-object', 'link' => false, 'htmlOptions' => ['class' => 'pull-left']]) ?>
            <?php endif; ?>

            <!-- Show space image, if you are outside from a space -->
            <?php if (!Yii::$app->controller instanceof ContentContainerController) : ?>
                <?php if ($record->content->container instanceof Space) : ?>
                    <?=
                    Image::widget([
                        'space' => $record->content->container,
                        'width' => 20,
                        'htmlOptions' => [
                            'class' => 'img-space pull-left',
                        ]
                    ])
                    ?>
                <?php endif; ?>

            <?php endif; ?>

            <div class="media-body text-break">

                <!-- Show content -->
                <?= $content ?>
                <br>

                <!-- show time -->
                <?= TimeAgo::widget(['timestamp' => $record->content->created_at]) ?>
            </div>
        </div>
    </li>
<?php if ($clickable) : ?>
</a>
<?php endif; ?>
