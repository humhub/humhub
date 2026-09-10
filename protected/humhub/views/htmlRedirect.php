<?php

use humhub\helpers\Html;
use yii\helpers\Json;

/* @var $url string */

// Encode once for a JavaScript string-literal context (this already includes the quotes).
$jsUrl = Json::htmlEncode($url);
// Remove test.php#xy (#xy) part, then encode the trimmed value as well.
$jsUrlWithoutHash = Json::htmlEncode(explode('#', (string) $url)[0]);
?>
<script <?= Html::nonce() ?>>
    // If current URL == New Url (absolute || relative) then only Refresh
    if (window.location.pathname + window.location.search + window.location.hash == <?= $jsUrl ?>
        || window.location.href == <?= $jsUrl ?>) {
        window.location.href = <?= $jsUrlWithoutHash ?>;
    } else {
        window.location.href = <?= $jsUrlWithoutHash ?>;
    }
</script>
