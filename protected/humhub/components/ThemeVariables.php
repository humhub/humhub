<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\components;

use humhub\helpers\ThemeHelper;
use humhub\services\ActiveThemeService;
use Throwable;
use Yii;
use yii\base\Component;

/**
 * ThemeVariables provides access to the SCSS variables of a given [[Theme]].
 *
 * For the active theme the variables come from the `theme.state` setting, which
 * {@see ActiveThemeService} keeps up to date - the base settings are loaded as one
 * blob on every request, so reading from them is free. Any other theme is read from
 * its SCSS files and not persisted, so that setting stays a single row.
 *
 * @since 1.3
 * @package humhub\components
 */
class ThemeVariables extends Component
{
    /**
     * @var Theme
     */
    public $theme;

    /**
     * @var array|null the variables of this theme, resolved once per instance
     */
    private ?array $all = null;

    /**
     * Returns a variable by given key
     *
     * @param $key
     * @param $default
     *
     * @return string|null
     */
    public function get($key, $default = null)
    {
        if (!Yii::$app->installationState->hasState(InstallationState::STATE_DATABASE_CREATED)) {
            return null;
        }

        if ($custom = $this->getCustom($key)) {
            return $custom;
        }

        return $this->getAll()[$key] ?? $default;
    }

    /**
     * Get theme variable value from customization settings form
     *
     * @param string $key
     * @return string|null
     */
    public function getCustom(string $key): ?string
    {
        return in_array($key, ['primary', 'accent', 'secondary', 'success', 'danger', 'warning', 'info', 'light', 'dark'])
            ? Yii::$app->settings->get('theme' . ucfirst($key) . 'Color')
            : null;
    }

    /**
     * Drops the state of the active theme - regardless of which theme this instance
     * wraps, since there is only ever one state and it always describes the active
     * theme. The variables memoized by this instance are dropped along with it.
     *
     * @see ActiveThemeService::flush()
     */
    public function flushCache()
    {
        $this->all = null;

        ActiveThemeService::flush();
    }

    /**
     * Returns all variables of this theme, resolved once per instance: for a theme that
     * is not the active one every call would otherwise re-parse the whole theme tree,
     * and a single view renders a dozen variables.
     */
    protected function getAll(): array
    {
        if ($this->all !== null) {
            return $this->all;
        }

        $state = ActiveThemeService::getState();

        if ($state !== null && $state['path'] === $this->theme->getBasePath()) {
            return $this->all = $state['vars'];
        }

        try {
            return $this->all = ThemeHelper::getAllVariables($this->theme);
        } catch (Throwable $e) {
            // The likely culprit is the admin's Custom SCSS, which is shared across every
            // theme - a broken snippet must not leave this theme without any variables.
            // A second failure means the theme's own files are broken and does propagate
            Yii::error('Could not read the theme variables of "' . $this->theme->name . '": ' . $e->getMessage(), 'ui');
            return $this->all = ThemeHelper::getAllVariables($this->theme, false);
        }
    }
}
