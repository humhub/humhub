<?php
use yii\bootstrap\Html;

if ($link == true) :
    echo Html::beginTag('a', $linkOptions);
endif;
echo Html::beginTag('div', $acronymHtmlOptions);
echo $acronym;
echo Html::endTag('div');
echo Html::img($space->getProfileImage()->getUrl(), $imageHtmlOptions);
if ($link == true) :
    echo Html::endTag('a');
endif;
?>
