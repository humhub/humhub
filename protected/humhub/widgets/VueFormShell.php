<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\widgets;

use Closure;
use humhub\components\Widget;
use humhub\widgets\form\ActiveForm;
use yii\base\InvalidConfigException;

/**
 * Renders a Yii `ActiveForm` shell for a Vue island: a bare `<form>` fragment whose fields are
 * entirely up to the caller, wrapped in the conventions such a shell needs to be rendered ONCE
 * server-side and cloned as many times as needed client-side - a page can host several
 * independent instances of the SAME shell at once (e.g. a create form plus several open
 * edit/reply forms), all clones of one server-rendered template. See
 * `humhub\vue\LegacyFormWrapper.vue`'s class docblock for the full client-side half of this
 * contract.
 *
 * Conventions baked into the rendered `<form>`:
 *  - `action => '#'`: no real submit target - the Vue island owns submission itself (typically
 *    posting a JSON API), and a static action avoids Yii falling back to the current request
 *    URL, which is unavailable outside a full web request (e.g. unit tests).
 *  - the CSRF input is disabled (`'csrf' => false` in `options`, which
 *    `yii\widgets\ActiveForm::run()`/`Html::beginForm()` read directly): a baked-in token would
 *    go stale for every clone anyway, and the island's own ajax submission gets a live token
 *    from `yii.js` regardless.
 *  - `acknowledge => true`: wires HumHub's "unsaved changes will be lost" guard onto the form
 *    (see {@see ActiveForm::$acknowledge}).
 *  - the `<form>` tag's own id is {@see self::id()}-derived.
 *
 * ## The `__VUEFORM__` token
 *
 * Every id the rendered markup declares OR references (`id`, `for`, and any CSS-id-selector
 * fragment embedded in a `data-*` attribute, e.g. a drop-zone or preview target) must be built
 * from the shared {@see self::TOKEN} via {@see self::id()} - the client
 * (`humhub\vue\LegacyFormWrapper.vue`) clones the rendered HTML string per Vue-island instance
 * by replacing every occurrence of that literal token with a unique per-instance id. **The
 * constant below and `LegacyFormWrapper.vue`'s own `FORM_TOKEN` constant are a mirrored pair
 * across languages - if the literal ever needs to change, update both.**
 *
 * ## Usage
 *
 * ```php
 * echo VueFormShell::widget([
 *     'content' => function (ActiveForm $form) use ($model) {
 *         return $form->field($model, 'title', [
 *             'options' => ['id' => VueFormShell::id('title-group')],
 *         ])->textInput(['id' => VueFormShell::id('title')])->label(false);
 *     },
 * ]);
 * ```
 *
 * `content` receives the `ActiveForm` instance this widget already began (with the conventions
 * above applied) and returns the fields' HTML; the widget itself owns `ActiveForm::begin()`/
 * `::end()` and nothing about the fields - it has no notion of what any particular caller's form
 * looks like. See `humhub\modules\comment\widgets\CommentFormShell` for a full reference
 * composition (richtext editor + file upload stack) built on top of this widget.
 *
 * @since 1.19
 */
class VueFormShell extends Widget
{
    /**
     * Placeholder every id in the rendered markup must be built from via {@see self::id()}.
     * Mirrored on the client as `FORM_TOKEN` in `humhub\vue\LegacyFormWrapper.vue` - keep both
     * literals in sync if either ever changes.
     */
    public const TOKEN = '__VUEFORM__';

    /**
     * @var Closure(ActiveForm $form): string renders the form's fields. Receives the
     *     `ActiveForm` instance this widget already began (conventions already applied) and
     *     must return the fields' HTML - this widget echoes nothing else inside the `<form>`.
     */
    public $content;

    /**
     * @var array additional {@see ActiveForm::begin()} options, merged over this widget's own
     *     conventions (`action`, `options.id`, `options.csrf`, `acknowledge`) - use e.g.
     *     `['options' => ['class' => 'my-form']]` to add extra attributes without losing them.
     */
    public array $formOptions = [];

    /**
     * @param string $suffix
     * @return string a {@see self::TOKEN}-derived id fragment, e.g. `VueFormShell::id('title')`
     *     === `'__VUEFORM___title'`. Use for every id/for/CSS-id-selector value a caller
     *     renders - both inside {@see self::$content} and in any markup a composing widget
     *     wraps around this one (e.g. a drop-zone container enclosing the whole shell).
     */
    public static function id(string $suffix): string
    {
        return self::TOKEN . '_' . $suffix;
    }

    /**
     * @return string
     * @throws InvalidConfigException if {@see self::$content} was not set to a closure.
     */
    public function run()
    {
        if (!$this->content instanceof Closure) {
            throw new InvalidConfigException('VueFormShell requires "content" to be a closure of signature function(ActiveForm $form): string.');
        }

        $options = array_replace_recursive([
            'action' => '#',
            'options' => ['id' => self::id('form'), 'csrf' => false],
            'acknowledge' => true,
        ], $this->formOptions);

        // try/finally so a throwing content closure can't leave a dangling output-buffer level
        // or an unbalanced ActiveForm::begin()/end() widget stack behind it for the rest of the
        // request - both `ob_start()` and `ActiveForm::begin()` push onto global stacks that
        // only their matching `ob_get_clean()`/`ActiveForm::end()` pop. `$began` guards the
        // `ActiveForm::end()` call itself: if `ActiveForm::begin()` never succeeded, the widget
        // was never pushed, and calling `end()` anyway would throw its own "no matching begin()"
        // exception, masking whatever actually went wrong.
        $began = false;
        ob_start();
        try {
            $form = ActiveForm::begin($options);
            $began = true;
            echo ($this->content)($form);
        } finally {
            if ($began) {
                ActiveForm::end();
            }
            $output = ob_get_clean();
        }

        return $output;
    }
}
