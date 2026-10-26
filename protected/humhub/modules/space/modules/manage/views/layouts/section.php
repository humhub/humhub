<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

use humhub\components\View;
use humhub\widgets\menu\Menu;
use humhub\widgets\PageHeader;

/**
 * A section of a space's settings (Settings, Security, Members, Modules): the page header with
 * the section's tabs, then the view's content cards.
 *
 * ```php
 * <?php $this->beginContent('@space/modules/manage/views/layouts/section.php', [
 *     'title' => Yii::t('SpaceModule.manage', 'Settings'),
 *     'tabs' => new DefaultMenu(['space' => $space]),
 * ]) ?>
 * <?php ContentCard::begin(['title' => Yii::t('SpaceModule.manage', 'Basic')]) ?> … <?php ContentCard::end() ?>
 * <?php $this->endContent() ?>
 * ```
 *
 * @var View $this
 * @var string $title the section's title
 * @var Menu|null $tabs the section's tabs
 * @var string $content
 * @since 1.20
 */
?>
<div class="d-flex flex-column gap-3">
    <?= PageHeader::title($title)->tabs($tabs ?? null) ?>
    <?= $content ?>
</div>
