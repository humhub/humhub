<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

use humhub\components\View;
use humhub\modules\notification\models\Notification;
use humhub\modules\space\models\Space;

/* @var View $this */
/* @var string $html */
/* @var User $originator */
/* @var Space $space */
/* @var Notification $record */
/* @var bool $isNew */
/* @var string $url */
/* @var string $relativeUrl */
?>
<?php $this->beginContent('@notification/views/layouts/web.php', [
    'originator' => $originator,
    'space' => $space,
    'record' => $record,
    'isNew' => $isNew,
    'url' => $url,
    'relativeUrl' => $relativeUrl,
]) ?>
<?= $html ?>
<?php $this->endContent() ?>
