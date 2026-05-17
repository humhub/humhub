<?php

use humhub\helpers\Html;
use humhub\models\forms\ChooseLanguage;

/**
 * @var $languages array
 * @var $model ChooseLanguage
 * @var $vertical bool
 */

if (count($languages) <= 1) {
    return;
}

$currentLanguage = Yii::$app->language;
$currentLabel = $languages[$currentLanguage] ?? reset($languages);
$wrapperId = 'language-chooser-' . substr(md5(uniqid('', true)), 0, 8);
$wrapperClasses = $vertical ? 'd-flex justify-content-center w-100' : 'd-inline-block';
?>

<div class="<?= $wrapperClasses ?> language-chooser animated fadeIn">
    <div class="dropdown" id="<?= $wrapperId ?>">
        <a href="#"
           class="language-chooser-toggle"
           role="button"
           data-bs-toggle="dropdown"
           data-bs-auto-close="outside"
           data-bs-display="static"
           aria-expanded="false">
            <i class="fa fa-globe" aria-hidden="true"></i>
            <span class="language-chooser-current"><?= Html::encode($currentLabel) ?></span>
        </a>
        <div class="dropdown-menu language-chooser-menu p-0">
            <form method="post" action="" data-pjax-prevent class="m-0">
                <?= Html::hiddenInput(Yii::$app->request->csrfParam, Yii::$app->request->getCsrfToken()) ?>
                <div class="language-chooser-search-wrap p-2 border-bottom">
                    <input type="text"
                           class="form-control form-control-sm language-chooser-search"
                           placeholder="<?= Yii::t('base', 'Search') ?>"
                           autocomplete="off"
                           aria-label="<?= Yii::t('base', 'Search') ?>">
                </div>
                <div class="language-chooser-list">
                    <?php foreach ($languages as $code => $label): ?>
                        <?php $isActive = $code === $currentLanguage; ?>
                        <button type="submit"
                                name="ChooseLanguage[language]"
                                value="<?= Html::encode($code) ?>"
                                class="dropdown-item language-chooser-item<?= $isActive ? ' active' : '' ?>"
                                data-language-search="<?= Html::encode(mb_strtolower($label . ' ' . $code)) ?>">
                            <?= Html::encode($label) ?>
                        </button>
                    <?php endforeach; ?>
                    <div class="language-chooser-empty p-2 text-muted small" hidden>
                        <?= Yii::t('base', 'No results found.') ?>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script <?= Html::nonce() ?>>
    (function () {
        var wrapper = document.getElementById('<?= $wrapperId ?>');
        if (!wrapper) {
            return;
        }
        var search = wrapper.querySelector('.language-chooser-search');
        var items = wrapper.querySelectorAll('.language-chooser-item');
        var empty = wrapper.querySelector('.language-chooser-empty');

        var menuEl = wrapper.querySelector('.language-chooser-menu');

        wrapper.addEventListener('show.bs.dropdown', function () {
            // Flip up when there isn't enough space below the toggle.
            wrapper.classList.remove('dropup');
            if (!menuEl) {
                return;
            }
            var toggle = wrapper.querySelector('.language-chooser-toggle');
            if (!toggle) {
                return;
            }
            var rect = toggle.getBoundingClientRect();
            // Measure menu height by briefly rendering it off-screen.
            menuEl.style.visibility = 'hidden';
            menuEl.style.display = 'block';
            var menuHeight = menuEl.offsetHeight;
            menuEl.style.display = '';
            menuEl.style.visibility = '';
            var spaceBelow = window.innerHeight - rect.bottom;
            if (spaceBelow < menuHeight && rect.top > spaceBelow) {
                wrapper.classList.add('dropup');
            }
        });

        wrapper.addEventListener('shown.bs.dropdown', function () {
            if (search) {
                search.value = '';
                items.forEach(function (item) { item.hidden = false; });
                if (empty) { empty.hidden = true; }
                search.focus();
            }
            var active = wrapper.querySelector('.language-chooser-item.active');
            if (active && active.scrollIntoView) {
                active.scrollIntoView({ block: 'nearest' });
            }
        });

        if (search) {
            search.addEventListener('input', function () {
                var query = search.value.trim().toLowerCase();
                var visible = 0;
                items.forEach(function (item) {
                    var haystack = item.getAttribute('data-language-search') || '';
                    var match = !query || haystack.indexOf(query) !== -1;
                    item.hidden = !match;
                    if (match) { visible++; }
                });
                if (empty) { empty.hidden = visible !== 0; }
            });

            search.addEventListener('keydown', function (e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    var firstVisible = Array.prototype.find.call(items, function (item) {
                        return !item.hidden;
                    });
                    if (firstVisible) {
                        firstVisible.click();
                    }
                }
            });
        }
    })();
</script>
