<?php

namespace humhub\helpers;

use humhub\modules\ui\icon\widgets\Icon;
use yii\helpers\Url;

/**
 * Added to Anchors and Buttons with some generic Options.
 *
 */
trait HtmlClickableElementTrait
{
    /**
     * @var Icon|null the icon to be displayed before the label.
     */
    public ?Icon $icon = null;

    abstract protected function &getOptionsRef(): array;

    /**
     * Adds a data-action-click handler to the button.
     */
    public function action(string $handler, $url = null, ?string $target = null): static
    {
        return $this->onAction('click', $handler, $url, $target);
    }

    /**
     * Adds a data-action-* handler to the button.
     */
    private function onAction(string $event, string $handler, $url = null, ?string $target = null): static
    {
        $this->getOptionsRef()['data-action-' . $event] = $handler;

        if ($url) {
            $this->getOptionsRef()['data-action-' . $event . '-url'] = Url::to($url);
        }

        if ($target) {
            $this->getOptionsRef()['data-action-' . $event . '-target'] = $target;
        }

        return $this;
    }

    /**
     * If set to false the [data-pjax-prevent] flag is attached to the link.
     */
    public function pjax(bool $pjax = true): static
    {
        if (!$pjax) {
            Html::addPjaxPrevention($this->getOptionsRef());
        }

        return $this;
    }

    /**
     * Adds a title + tooltip behaviour data
     */
    public function tooltip(?string $title): static
    {
        if ($title !== null && $title !== '') {
            $this->getOptionsRef()['data-bs-title'] = $title;
            $this->getOptionsRef()['data-bs-toggle'] = 'tooltip';
        }

        return $this;
    }

    public function disablePjax(): self
    {
        $this->getOptionsRef()['data-pjax-prevent'] = 1;
        return $this;
    }

    public function isPjaxEnabled(): bool
    {
        return Html::isPjaxEnabled($this->options);
    }

    /**
     * Adds a confirmation behaviour to the button.
     */
    public function confirm(
        ?string $title = null,
        ?string $body = null,
        ?string $confirmButtonText = null,
        ?string $cancelButtonText = null,
    ): static {
        if ($title) {
            $this->getOptionsRef()['data-action-confirm-header'] = $title;
        }

        if ($body) {
            $this->getOptionsRef()['data-action-confirm'] = $body;
        } else {
            $this->getOptionsRef()['data-action-confirm'] = '';
        }

        if ($confirmButtonText) {
            $this->getOptionsRef()['data-action-confirm-text'] = $confirmButtonText;
        }

        if ($cancelButtonText) {
            $this->getOptionsRef()['data-action-cancel-text'] = $cancelButtonText;
        }

        return $this;
    }

    public function icon(string|Icon $icon, bool $right = false, $options = []): static
    {
        // Extract icon from FontAwesome 4 HTML element
        // TODO: remove later ($icon should be the name of the Icon or an instance of Icon)
        $matches = [];
        if (is_string($icon) && preg_match('/fa-([a-z0-9-]+)/', $icon, $matches)) {
            $icon = $matches[1] ?? null;
        }

        $this->icon = ($icon instanceof Icon) ? $icon : Icon::get($icon, $options);

        if ($right) {
            $this->icon->right();
        }

        return $this;
    }
}
