<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\components\icon;

use humhub\helpers\Html;
use humhub\widgets\Icon;
use Yii;

/**
 * Renders icons with the Tabler Icons webfont as `<i class="ti ti-<name>">`.
 *
 * Names are Tabler names (https://tabler.io/icons); the filled variants are addressed as
 * `<name>-filled`. A Font Awesome 4 name is translated through [[LegacyIconMap]] so that modules
 * written against the previous icon library keep rendering, and a name neither library knows is
 * rendered as given.
 *
 * The available names and their codepoints are read once from the SCSS shipped with the
 * `@tabler/icons-webfont` package and cached per package version, see [[getCodepoints()]].
 *
 * @since 1.20
 */
class TablerIconProvider implements IconProvider
{
    public const ID = 'ti';

    public const FILLED_SUFFIX = '-filled';

    private static ?array $codepoints = null;

    /**
     * @inheritdoc
     */
    public function getId()
    {
        return self::ID;
    }

    /**
     * @param Icon|string|array $icon
     * @inheritdoc
     */
    public function render($icon, $options = [])
    {
        $icon = Icon::get($icon, $options);
        $options = $icon->htmlOptions;

        Html::addCssClass($options, 'ti ti-' . static::resolveName($icon->name));

        if ($icon->size && ($sizeClass = $this->getIconSizeClass($icon))) {
            Html::addCssClass($options, $sizeClass);
        }

        if ($icon->fixedWidth) {
            Html::addCssClass($options, 'icon-fw');
        }

        if ($icon->right) {
            Html::addCssClass($options, 'icon-pull-right');
        }

        if ($icon->left) {
            Html::addCssClass($options, 'icon-pull-left');
        }

        $options['aria-hidden'] = 'true';

        $ariaElement = '';
        if ($icon->tooltip) {
            $options['role'] = 'img';
            Html::addTooltip($options, $icon->tooltip);
            $ariaElement = Html::tag('span', $icon->ariaLabel ?? $icon->tooltip, ['class' => 'visually-hidden']);
        }

        if ($icon->color) {
            Html::addCssStyle($options, ['color' => $icon->color]);
        }

        return Html::tag('i', '', $options) . $ariaElement;
    }

    /**
     * Resolves whatever name a caller passed to the Tabler name that is rendered: strips a `fa-` or
     * `ti-` class prefix, applies the `icon.alias` parameter and then [[resolveLegacyName()]].
     */
    public static function resolveName(?string $name): string
    {
        $name = Icon::resolveAlias(Icon::stripPrefix($name)) ?? '';
        $resolved = static::resolveLegacyName($name);

        if ($resolved !== $name) {
            Yii::debug("Icon '$name' is a Font Awesome 4 name, rendered as Tabler icon '$resolved'", 'icon');
        } elseif (YII_DEBUG && $name !== '' && !static::hasName($name)) {
            Yii::debug("Icon '$name' is not a Tabler icon name", 'icon');
        }

        return $resolved;
    }

    /**
     * Translates a Font Awesome 4 name through the [[LegacyIconMap]] — unless the name is a Tabler
     * name as well. `star`, `user`, `trash` or `caret-right` exist in both libraries, and since Tabler
     * names are the canonical ones the Tabler icon wins: such a legacy name renders the outline
     * glyph, not the filled variant `css/icon-legacy.css` uses for the unambiguous `fa-star` class.
     */
    public static function resolveLegacyName(string $name): string
    {
        if (!LegacyIconMap::isLegacyName($name) || static::hasName($name)) {
            return $name;
        }

        return LegacyIconMap::resolve($name);
    }

    /**
     * @inheritdoc
     */
    public function renderList($listDefinition)
    {
        return null;
    }

    /**
     * @inheritdoc
     * @return string[] outline names plus the filled names with the `-filled` suffix
     */
    public function getNames()
    {
        $codepoints = static::getCodepoints();

        return array_merge(
            array_keys($codepoints['outline']),
            array_map(fn(string $name) => $name . self::FILLED_SUFFIX, array_keys($codepoints['filled'])),
        );
    }

    public static function hasName(string $name): bool
    {
        $codepoints = static::getCodepoints();

        if (str_ends_with($name, self::FILLED_SUFFIX)) {
            return isset($codepoints['filled'][substr($name, 0, -strlen(self::FILLED_SUFFIX))]);
        }

        return isset($codepoints['outline'][$name]);
    }

    /**
     * Icon names and their hexadecimal codepoints as shipped by the installed package.
     *
     * @return array{outline: array<string, string>, filled: array<string, string>}
     */
    public static function getCodepoints(): array
    {
        if (static::$codepoints !== null) {
            return static::$codepoints;
        }

        $cacheKey = [self::class, static::getPackageVersion()];
        $codepoints = Yii::$app->cache->get($cacheKey);

        if (!is_array($codepoints)) {
            $codepoints = [
                'outline' => static::parseCodepoints(static::getDistPath() . '/tabler-icons.scss'),
                'filled' => static::parseCodepoints(static::getDistPath() . '/tabler-icons-filled.scss'),
            ];
            Yii::$app->cache->set($cacheKey, $codepoints);
        }

        return static::$codepoints = $codepoints;
    }

    public static function getDistPath(): string
    {
        return Yii::getAlias('@npm/tabler--icons-webfont/dist');
    }

    public static function getPackageVersion(): string
    {
        $package = json_decode((string)file_get_contents(static::getDistPath() . '/../package.json'), true);

        return (string)($package['version'] ?? '0');
    }

    /**
     * @return array<string, string> name => codepoint from the `$ti-icon-<name>: unicode('<hex>')` variables
     */
    private static function parseCodepoints(string $scssFile): array
    {
        preg_match_all("/\\\$ti-icon-([a-z0-9-]+):\s*unicode\('([0-9a-f]+)'\)/", (string)file_get_contents($scssFile), $matches, PREG_SET_ORDER);

        $codepoints = [];
        foreach ($matches as $match) {
            $codepoints[$match[1]] = $match[2];
        }

        return $codepoints;
    }

    private function getIconSizeClass(Icon $icon): ?string
    {
        return match ($icon->size) {
            Icon::SIZE_XS => 'icon-xs',
            Icon::SIZE_SM => 'icon-sm',
            Icon::SIZE_LG => 'icon-lg',
            Icon::SIZE_2x => 'icon-2x',
            Icon::SIZE_3x => 'icon-3x',
            Icon::SIZE_4x => 'icon-4x',
            Icon::SIZE_5x => 'icon-5x',
            Icon::SIZE_6x => 'icon-6x',
            Icon::SIZE_7x => 'icon-7x',
            Icon::SIZE_8x => 'icon-8x',
            Icon::SIZE_9x => 'icon-9x',
            Icon::SIZE_10x => 'icon-10x',
            default => null,
        };
    }
}
