<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

use humhub\widgets\PageHeader;

/**
 * What the marketplace page shows until its island mounts — the page header with the
 * title and the skeleton grid the island renders while its first page loads (the markup of
 * `EntityBrowser.vue`, `PageHeader.vue`, `EntityGrid.vue` and `CardSkeleton.vue`).
 */
?>
<div class="marketplace">
    <div class="entity-browser">
        <?= PageHeader::title(Yii::t('MarketplaceModule.base', 'Marketplace')) ?>
        <div class="entity-grid">
            <div class="entity-grid-cells" aria-busy="true">
                <?php for ($i = 0; $i < 12; $i++) : ?>
                    <div class="entity-grid-cell entity-grid-cell-skeleton" style="--card-stagger-index: <?= $i ?>">
                        <div class="card entity-card entity-card-skeleton placeholder-glow" aria-hidden="true">
                            <div class="entity-card-cover entity-card-cover-plain">
                                <span class="placeholder entity-card-skeleton-image"></span>
                            </div>
                            <div class="entity-card-header">
                                <span class="placeholder entity-card-skeleton-title"></span>
                                <span class="placeholder entity-card-skeleton-subtitle"></span>
                            </div>
                            <div class="entity-card-body">
                                <span class="placeholder entity-card-skeleton-line"></span>
                                <span class="placeholder entity-card-skeleton-line entity-card-skeleton-line-short"></span>
                            </div>
                            <div class="entity-card-footer">
                                <span class="placeholder entity-card-skeleton-action"></span>
                                <span class="placeholder entity-card-skeleton-icon"></span>
                            </div>
                        </div>
                    </div>
                <?php endfor; ?>
            </div>
        </div>
    </div>
</div>
