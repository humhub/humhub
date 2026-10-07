<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

use humhub\components\View;
use humhub\modules\notification\targets\DeliveryBatch;
use yii\helpers\Url;

/**
 * The plaintext notification mail of a {@see DeliveryBatch}, inside the global text mail layout:
 * per notification its sentence, its mail body if any and, on the next line, its entry URL.
 * Several notifications get the subject as headline and the overview URL at the end.
 */

/* @var View $this */
/* @var DeliveryBatch $batch */

$lines = [];
if (!$batch->isSingle()) {
    $lines[] = $batch->getSubject();
    $lines[] = '';
}
foreach ($batch->notifications as $notification) {
    $lines[] = $notification->asMailText();
    if (($body = $notification->getMailBody()) !== null) {
        $lines[] = $body;
    }
    $lines[] = $notification->getEntryUrl();
    $lines[] = '';
}
if (!$batch->isSingle()) {
    $lines[] = Yii::t('NotificationModule.base', 'Open notifications') . ': ' . Url::to(['/notification/overview'], true);
}

echo rtrim(implode("\n", $lines)) . "\n";
