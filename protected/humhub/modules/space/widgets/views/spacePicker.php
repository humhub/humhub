<?php

/**
 * This View replaces an input with a space picker
 *
 * @property String $inputId is the ID of the input HTML Element
 * @property Int $maxSpaces the maximum of spaces for this input
 * @property String $spaceSearchUrl the url of the search, to find the spaces
 * @property String $currentValue is the current value of the parent field.
 *
 * @package humhub.modules_core.user
 * @since 0.5
 */
use yii\helpers\Html;
use humhub\modules\space\models\Space;
use humhub\modules\space\widgets\Image;

$this->registerJsFile('@web/resources/space/spacepicker.js', ['position'=>\yii\web\View::POS_END]);
?>

<?php
// Resolve guids to space tags
$selectedSpaces = "";

foreach (explode(",", $currentValue) as $guid) {
    $space = Space::findOne(['guid' => trim($guid)]);
    if ($space != null) {
        $name = Html::encode($space->name);
        $selectedSpaces .= '<li class="spaceInput" id="' . $space->guid . '">' . Image::widget(["space" => $space, "width" => 24]) . ' ' . addslashes($name) . '<i class="fa fa-times-circle"></i></li>';
    }
}
?>


<script type="text/javascript">

    $(function() {
        $('#<?php echo $inputId; ?>').spacepicker({
            inputId: '#<?php echo $inputId; ?>',
            maxSpaces: '<?php echo $maxSpaces; ?>',
            searchUrl: '<?php echo $spaceSearchUrl; ?>',
            currentValue: '<?php echo str_replace("\n", " \\", $selectedSpaces); ?>'
        });
    });

</script>