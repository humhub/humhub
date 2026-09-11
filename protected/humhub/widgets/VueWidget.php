<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\widgets;

use humhub\components\Widget;

/**
 * Base class of a PHP widget whose output is a Vue island.
 *
 * The widget keeps its public API — callers, themes and modules keep calling
 * `LikeLink::widget(['object' => $post])` — and renders the island's mount point through
 * {@see VueComponent} instead of a PHP view. A subclass declares the component and the bundle
 * shipping it, and hands over what only the server knows: props ({@see getProps()}), the
 * attributes of the mount element ({@see getOptions()}) and a placeholder shown until the
 * island mounts ({@see getPlaceholder()}). Returning `false` from `beforeRun()` renders
 * nothing (guests, empty data, …), exactly as with any widget.
 *
 * See docs/develop/ui-js-vuejs-components.md, "Using components from PHP".
 *
 * @since 1.20
 */
abstract class VueWidget extends Widget
{
    /**
     * @var string registered Vue component name, e.g. `LikeButton`
     */
    protected string $component = '';

    /**
     * @var string|null asset bundle providing the compiled component; `null` for a component
     * of the core set, which every page loads
     */
    protected ?string $assetBundle = null;

    /**
     * The props handed to the island — see {@see VueComponent::$props} for how they are encoded.
     */
    protected function getProps(): array
    {
        return [];
    }

    /**
     * HTML attributes of the mount element. Give it the id and classes the server-rendered
     * element had, so theme CSS, the product tour and tests keep addressing it.
     */
    protected function getOptions(): array
    {
        return [];
    }

    /**
     * Markup shown until the island mounts — an empty custom element has no size, so anything
     * measuring it before then sees nothing.
     */
    protected function getPlaceholder(): string
    {
        return '';
    }

    /**
     * @inheritdoc
     */
    public function run()
    {
        return VueComponent::widget([
            'name' => $this->component,
            'assetBundle' => $this->assetBundle,
            'props' => $this->getProps(),
            'options' => $this->getOptions(),
            'content' => $this->getPlaceholder(),
        ]);
    }
}
