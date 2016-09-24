<?php echo strip_tags(Yii::t('base', '<strong>Latest</strong> updates')); ?>


<?php

if ($notifications_plaintext != '') {
    echo $notifications_plaintext;
} else if ($activities_plaintext != '') {
    echo $activities_plaintext;
}

?>
