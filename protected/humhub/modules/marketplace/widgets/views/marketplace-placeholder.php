<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

/**
 * What the marketplace page shows until its island mounts — the page toolbar with the
 * title and the skeleton grid the island renders while its first page loads (the markup of
 * `CardDirectory.vue`, `PageToolbar.vue`, `CardGrid.vue` and `CardSkeleton.vue`).
 */
?>
<div class="c-marketplace">
    <div class="c-card-directory">
        <section class="c-page-toolbar" aria-labelledby="marketplace-placeholder-title">
            <div class="c-page-toolbar__header">
                <h1 id="marketplace-placeholder-title" class="c-page-toolbar__title"><?= Yii::t('MarketplaceModule.base', 'Marketplace') ?></h1>
            </div>
        </section>
        <div class="c-card-grid">
            <div class="c-card-grid__cells" aria-busy="true">
                <?php for ($i = 0; $i < 12; $i++) : ?>
                    <div class="c-card-grid__cell c-card-grid__cell--skeleton" style="--card-stagger-index: <?= $i ?>">
                        <div class="c-card-skeleton" aria-hidden="true">
                            <div class="c-card-skeleton__cover">
                                <span class="c-card-skeleton__block c-card-skeleton__image"></span>
                            </div>
                            <div class="c-card-skeleton__header">
                                <span class="c-card-skeleton__block c-card-skeleton__title"></span>
                                <span class="c-card-skeleton__block c-card-skeleton__version"></span>
                            </div>
                            <div class="c-card-skeleton__body">
                                <span class="c-card-skeleton__block c-card-skeleton__line"></span>
                                <span class="c-card-skeleton__block c-card-skeleton__line c-card-skeleton__line--short"></span>
                            </div>
                            <div class="c-card-skeleton__footer">
                                <span class="c-card-skeleton__block c-card-skeleton__action"></span>
                                <span class="c-card-skeleton__block c-card-skeleton__icon"></span>
                            </div>
                        </div>
                    </div>
                <?php endfor; ?>
            </div>
        </div>
    </div>
</div>
