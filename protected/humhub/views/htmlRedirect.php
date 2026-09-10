<?php

use humhub\helpers\Html;
use yii\helpers\Json;

/* @var $url string */

// Remove test.php#xy (#xy) part, then encode for a JavaScript string-literal context
// (Json::htmlEncode already includes the quotes and is safe to embed in <script>).
?>
<script <?= Html::nonce() ?>>
    window.location.href = <?= Json::htmlEncode(explode('#', (string) $url)[0]) ?>;
</script>
