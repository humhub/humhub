<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

use humhub\components\View;
use humhub\modules\notification\components\NotificationPrivacy;
use humhub\modules\notification\services\NotificationBlockRenderer;
use humhub\modules\notification\targets\DeliveryBatch;
use yii\helpers\Url;

/**
 * The plaintext notification mail of a {@see DeliveryBatch}, inside the global text mail layout.
 *
 * One notification: its sentence and its blocks ({@see NotificationBlockRenderer}), buttons as
 * `label: URL` lines, separated by empty lines. Several: the subject as headline, per
 * notification its sentence and, on the next line, its entry URL, and the overview URL at the end.
 * The sentences and blocks follow the {@see NotificationPrivacy}, see the HTML twin.
 */

/* @var View $this */
/* @var DeliveryBatch $batch */

$lines = [];
if ($batch->isSingle()) {
    $notification = $batch->first();
    $renderer = new NotificationBlockRenderer($notification);

    $lines[] = $batch->getSentence($notification);
    $lines[] = '';
    foreach ($renderer->renderMailText($batch->getBlocks($notification)) as $part) {
        $lines[] = $part;
        $lines[] = '';
    }
} else {
    $lines[] = $batch->getSubject();
    $lines[] = '';
    if ($batch->privacy === NotificationPrivacy::Minimal) {
        array_push($lines, ...$batch->getCategorySummary());
        $lines[] = '';
    } else {
        foreach ($batch->notifications as $notification) {
            $lines[] = $batch->getSentence($notification);
            $lines[] = $notification->getEntryUrl();
            $lines[] = '';
        }
    }
    $lines[] = Yii::t('NotificationModule.base', 'Open notifications') . ': ' . Url::to(['/notification/overview'], true);
}

echo rtrim(implode("\n", $lines)) . "\n";
