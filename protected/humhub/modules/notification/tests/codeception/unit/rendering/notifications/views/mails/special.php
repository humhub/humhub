<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

use humhub\components\View;

/* @var View $this */
/* @var string $html */
?>
<?php $this->beginContent('@notification/tests/codeception/unit/rendering/notifications/views/layouts/specialLayout.php') ?>
<div>Special:<?= $html ?></div>
<?php $this->endContent() ?>
