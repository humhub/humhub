<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

use humhub\helpers\Html;

/**
 * What the spaces directory shows until its island mounts — the page toolbar with the title
 * and the actions, and the skeleton grid the island renders while its first page loads (the
 * markup of `CardDirectory.vue`, `PageToolbar.vue`, `CardGrid.vue` and `SpaceCardSkeleton.vue`).
 * The actions are inert here: the island takes over before they are needed.
 *
 * @var array $actions `SpaceDirectoryHeadingButtons::getEntriesData()`
 */
?>
<div class="c-space-directory">
    <div class="c-card-directory">
        <section class="c-page-toolbar" aria-labelledby="space-directory-placeholder-title">
            <div class="c-page-toolbar__header">
                <h1 id="space-directory-placeholder-title" class="c-page-toolbar__title"><?= Yii::t('SpaceModule.base', 'Spaces') ?></h1>
                <?php if ($actions !== []) : ?>
                    <div class="c-page-toolbar__actions">
                        <?php foreach ($actions as $action) : ?>
                            <?= Html::tag('span', Html::tag('i', '', ['class' => 'ti ti-' . (string)$action['icon'], 'aria-hidden' => 'true']), [
                                'class' => 'btn btn-' . ($action['variant'] ?? 'secondary') . ' c-icon-button disabled',
                                'title' => $action['label'],
                                'aria-hidden' => 'true',
                            ]) ?>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </section>
        <div class="c-card-grid">
            <div class="c-card-grid__cells" aria-busy="true">
                <?php for ($i = 0; $i < 12; $i++) : ?>
                    <div class="c-card-grid__cell c-card-grid__cell--skeleton" style="--card-stagger-index: <?= $i ?>">
                        <div class="c-card-skeleton c-space-card-skeleton" aria-hidden="true">
                            <div class="c-space-card-skeleton__cover">
                                <span class="c-card-skeleton__block c-space-card-skeleton__avatar"></span>
                            </div>
                            <div class="c-card-skeleton__header">
                                <span class="c-card-skeleton__block c-card-skeleton__title"></span>
                            </div>
                            <div class="c-card-skeleton__body">
                                <span class="c-card-skeleton__block c-card-skeleton__line"></span>
                                <span class="c-card-skeleton__block c-card-skeleton__line"></span>
                                <span class="c-card-skeleton__block c-card-skeleton__line c-card-skeleton__line--short"></span>
                            </div>
                            <div class="c-space-card-skeleton__tags">
                                <span class="c-card-skeleton__block c-space-card-skeleton__tag"></span>
                                <span class="c-card-skeleton__block c-space-card-skeleton__tag"></span>
                                <span class="c-card-skeleton__block c-space-card-skeleton__tag"></span>
                            </div>
                            <div class="c-card-skeleton__footer">
                                <span class="c-card-skeleton__block c-card-skeleton__action"></span>
                            </div>
                        </div>
                    </div>
                <?php endfor; ?>
            </div>
        </div>
    </div>
</div>
