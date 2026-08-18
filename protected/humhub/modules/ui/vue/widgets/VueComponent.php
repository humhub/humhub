<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\ui\vue\widgets;

use humhub\helpers\Html;
use yii\base\InvalidConfigException;
use yii\base\Widget;
use yii\helpers\Json;

/**
 * Renders the mount point for a client-side Vue component ("island") and
 * registers the asset bundle providing its compiled artifact.
 *
 * Scalar props are rendered as individual kebab-case attributes and type-coerced
 * client-side from the component's prop declarations; complex props are JSON
 * encoded into a single `props` attribute. No inline script is ever emitted -
 * props live in attributes, keeping CSP nonces and PJAX re-execution out of
 * the picture.
 *
 * See docs/develop/ui-js-vuejs.md
 *
 * @since 1.19
 */
class VueComponent extends Widget
{
    /**
     * @var string registered Vue component name - PascalCase whose derived tag
     * contains a dash, e.g. `LikeButton` (tag `<like-button>`)
     */
    public string $name = '';

    /**
     * @var array props passed to the component. Keys map directly onto HTML
     * attribute names (or, for complex values, onto keys of the JSON-encoded
     * `props` attribute) and must therefore be static, developer-controlled
     * strings - never derived from user input.
     */
    public array $props = [];

    /**
     * @var string|null asset bundle class providing the compiled Vue artifact of this component
     */
    public ?string $assetBundle = null;

    /**
     * @var string placeholder markup shown until the component is mounted
     */
    public string $content = '';

    /**
     * @var array additional HTML attributes for the mount tag
     */
    public array $options = [];

    /**
     * @inheritdoc
     */
    public function run()
    {
        $tag = self::toAttributeName($this->name);

        if (!preg_match('/^[A-Z][A-Za-z0-9]*$/', $this->name) || !str_contains($tag, '-')) {
            throw new InvalidConfigException('Vue component name must be PascalCase with a dashed tag form, got: ' . $this->name);
        }

        if ($this->assetBundle !== null) {
            ($this->assetBundle)::register($this->view);
        }

        $options = $this->options;
        $complexProps = [];

        foreach ($this->props as $prop => $value) {
            $attribute = self::toAttributeName($prop);

            if (in_array($attribute, ['class', 'id', 'style', 'props'], true) || str_starts_with($attribute, 'data-')) {
                throw new InvalidConfigException('Vue prop "' . $prop . '" maps to reserved attribute "' . $attribute . '" which the client-side registry does not read as a prop - rename the prop');
            }

            if (is_scalar($value)) {
                if (array_key_exists($attribute, $options)) {
                    throw new InvalidConfigException('Vue prop "' . $prop . '" collides with the "' . $attribute . '" entry in $options');
                }
                $options[$attribute] = is_bool($value) ? ($value ? 'true' : 'false') : (string)$value;
            } elseif ($value !== null) {
                $complexProps[$prop] = $value;
            }
        }

        if ($complexProps !== []) {
            if (array_key_exists('props', $options)) {
                throw new InvalidConfigException('Vue component "props" attribute is reserved for JSON-encoded complex props and cannot also be set via $options');
            }
            $options['props'] = Json::encode($complexProps);
        }

        return Html::tag($tag, $this->content, $options);
    }

    /**
     * Converts a PascalCase/camelCase name to its kebab-case tag/attribute form.
     * Mirrors the tag derivation of the client-side registry (humhub.vue.js toTagName()).
     */
    public static function toAttributeName(string $name): string
    {
        // word boundary: lowercase/digit followed by uppercase (likeButton -> like-Button)
        $name = preg_replace('/([a-z0-9])([A-Z])/', '$1-$2', $name);
        // word boundary inside a run of capitals, before the run's last letter
        // starts a new (lowercase-led) word (PDFViewer -> PDF-Viewer)
        $name = preg_replace('/([A-Z])([A-Z][a-z])/', '$1-$2', $name);

        return strtolower($name);
    }
}
