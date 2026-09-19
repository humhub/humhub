<?php

/*
 * @link      https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license   https://www.humhub.com/licences
 */

namespace humhub\tests\codeception\unit\modules\admin;

use humhub\components\Theme;
use humhub\helpers\ThemeHelper;
use humhub\modules\admin\libs\CacheHelper;
use humhub\services\ActiveThemeService;
use tests\codeception\_support\HumHubDbTestCase;
use Yii;

/**
 * @see CacheHelper
 * @since 1.20
 */
class CacheHelperTest extends HumHubDbTestCase
{
    protected $fixtureConfig = ['default'];

    private ?Theme $originalTheme = null;

    protected function _before()
    {
        parent::_before();

        $this->originalTheme = Yii::$app->view->theme;
    }

    protected function _after()
    {
        if ($this->originalTheme !== null) {
            Yii::$app->view->theme = $this->originalTheme;
        }

        ActiveThemeService::flush();

        parent::_after();
    }

    /**
     * Flushing the caches must not turn the fallback theme of the current request into
     * the stored selection. The web updater flushes in the same request in which the
     * theme could not be resolved yet, so persisting it here would silently replace the
     * admin's theme with the core theme - and the same happens whenever an admin presses
     * "Flush caches" while the module providing the theme is disabled.
     */
    public function testFlushCacheKeepsAThemeSelectionItCannotResolve()
    {
        // The situation ActiveThemeService::build() leaves behind for an unresolvable
        // name: the choice stays stored, the request renders with the core theme
        Yii::$app->settings->set('theme', 'DoesNotExist');
        Yii::$app->view->theme = ThemeHelper::getThemeByName(Theme::CORE_THEME_NAME);
        ActiveThemeService::flush();

        CacheHelper::flushCache();

        $this->assertSame('DoesNotExist', Yii::$app->settings->get('theme'));
        $this->assertNull(Yii::$app->settings->get(ActiveThemeService::SETTING_KEY));
    }
}
