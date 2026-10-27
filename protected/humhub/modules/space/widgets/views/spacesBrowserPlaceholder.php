<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

use humhub\modules\space\widgets\SpacesHeaderActions;
use humhub\widgets\PageHeader;

/**
 * What the spaces browser shows until its island mounts — the page header with the title
 * and the actions, and the skeleton grid the island renders while its first page loads (the
 * markup of `EntityBrowser.vue`, `PageHeader.vue`, `EntityGrid.vue` and `SpaceCardSkeleton.vue`).
 *
 * @var SpacesHeaderActions $headerActions
 */
?>
<div class="spaces-browser">
    <div class="entity-browser">
        <?= PageHeader::title(Yii::t('SpaceModule.base', 'Spaces'))->actions($headerActions) ?>
        <div class="entity-grid">
            <div class="entity-grid-cells" aria-busy="true">
                <?php for ($i = 0; $i < 12; $i++) : ?>
                    <div class="entity-grid-cell entity-grid-cell-skeleton" style="--card-stagger-index: <?= $i ?>">
                        <div class="card entity-card entity-card-skeleton space-card-skeleton placeholder-glow" aria-hidden="true">
                            <div class="entity-card-cover">
                                <span class="placeholder entity-card-skeleton-avatar"></span>
                            </div>
                            <div class="entity-card-header">
                                <span class="placeholder entity-card-skeleton-title"></span>
                            </div>
                            <div class="entity-card-body">
                                <span class="placeholder entity-card-skeleton-line"></span>
                                <span class="placeholder entity-card-skeleton-line"></span>
                                <span class="placeholder entity-card-skeleton-line entity-card-skeleton-line-short"></span>
                            </div>
                            <div class="entity-card-tags">
                                <span class="placeholder entity-card-skeleton-tag"></span>
                                <span class="placeholder entity-card-skeleton-tag"></span>
                                <span class="placeholder entity-card-skeleton-tag"></span>
                            </div>
                            <div class="entity-card-footer">
                                <span class="placeholder entity-card-skeleton-action"></span>
                            </div>
                        </div>
                    </div>
                <?php endfor; ?>
            </div>
        </div>
    </div>
</div>
