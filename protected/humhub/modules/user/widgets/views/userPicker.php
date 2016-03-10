<?php

/**
 * This View replaces a input with an user picker
 *
 * @property String $inputId is the ID of the input HTML Element
 * @property Int $maxUsers the maximum of users for this input
 * @property String $userSearchUrl the url of the search, to find the users
 * @property String $currentValue is the current value of the parent field.
 *
 * @package humhub.modules_core.user
 * @since 0.5
 */
use \humhub\modules\user\models\User;
use \yii\helpers\Html;

$this->registerJsFile("@web/js/jquery.highlight.min.js");
$this->registerJsFile("@web/resources/user/userpicker.js");
?>

<?php
// Resolve guids to user tags
$newValue = "";

foreach (explode(",", $currentValue) as $guid) {
    $user = User::findOne(['guid' => trim($guid)]);
    if ($user != null) {
        $imageUrl = $user->getProfileImage()->getUrl();
        $name = Html::encode($user->displayName);
        $newValue .= '<li class="userInput" id="' . $user->guid . '"><img class="img-rounded" alt="24x24" data-src="holder.js/24x24" style="width: 24px; height: 24px;" src="' . $imageUrl . '" alt="' . $name . 'r" width="24" height="24">' . $name . '<i class="fa fa-times-circle"></i></li>';
    }
}
?>

<script type="text/javascript">
    $(document).ready(function () {
        $('#<?php echo $inputId; ?>').userpicker({
            inputId: '#<?php echo $inputId; ?>',
            maxUsers: '<?php echo $maxUsers; ?>',
            searchUrl: '<?php echo $userSearchUrl; ?>',
            currentValue: '<?php echo $newValue; ?>',
            focus: '<?php echo $focus; ?>',
            userGuid: '<?php echo $userGuid; ?>',
            data: <?php echo $data ?>,
            placeholderText: '<?php echo $placeholderText; ?>'
        });
    });
</script>