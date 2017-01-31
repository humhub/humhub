<?php

use yii\helpers\Html;
use humhub\modules\space\widgets\Image;

$this->registerJsFile('@web-static/resources/space/spacepicker.js', ['position' => \yii\web\View::POS_END]);
?>

<?php
// Resolve guids to space tags
$selectedSpaces = "";
foreach ($spaces as $space) {
    $name = Html::encode($space->name);
    $selectedSpaces .= '<li class="spaceInput" id="' . $space->guid . '">' . Image::widget(["space" => $space, "width" => 24]) . ' ' . addslashes($name) . '<i class="fa fa-times-circle"></i></li>';
}
?>

<script type="text/javascript">
    $(function () {
        $('#<?php echo $inputId; ?>').spacepicker({
            inputId: '#<?php echo $inputId; ?>',
            maxSpaces: '<?php echo $maxSpaces; ?>',
            searchUrl: '<?php echo $spaceSearchUrl; ?>',
            currentValue: '<?php echo str_replace("\n", " \\", $selectedSpaces); ?>',
            placeholder: '<?= Html::encode($placeholder); ?>'
        });
    });
</script>