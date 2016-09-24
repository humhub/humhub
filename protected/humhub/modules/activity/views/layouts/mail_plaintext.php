<?php

use yii\helpers\Html;
?>

<?php echo $content; ?>

<?php if (isset($record->content->space) && $record->content->space !== null): ?>(<?php echo strip_tags(Yii::t('ActivityModule.views_activityLayoutMail', 'via')); ?> <?php echo Html::encode($record->content->space->name); ?>)

<?php endif; ?><?php if ($url != "") : ?><?php echo strip_tags(Yii::t('ActivityModule.views_activityLayoutMail', 'see online')); ?>: <?php echo urldecode($url); ?><?php endif; ?>


