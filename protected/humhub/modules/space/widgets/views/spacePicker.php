<?php

use humhub\modules\space\widgets\Image;
use yii\helpers\Html;
use yii\web\View;

$this->registerJsFile('@web-static/resources/space/spacepicker.js', ['position' => View::POS_END]);

// Resolve guids to space tags
$selectedSpaces = '';
foreach ($spaces as $space) {
    $name = Html::encode($space->name);
    $selectedSpaces .= '<li class="spaceInput" id="' . $space->guid . '">' . Image::widget(['space' => $space, 'width' => 24]) . ' ' . addslashes($name) . '<i class="fa fa-times-circle"></i></li>';
}
?>

<script <?= \humhub\libs\Html::nonce() ?>>
    $(function () {
        $('#<?= $inputId; ?>').spacepicker({
            inputId: '#<?= $inputId; ?>',
            maxSpaces: '<?= $maxSpaces; ?>',
            searchUrl: '<?= $spaceSearchUrl; ?>',
            currentValue: '<?= str_replace("\n", " \\", $selectedSpaces); ?>',
            placeholder: '<?= Html::encode($placeholder); ?>'
        });
    });
</script>
