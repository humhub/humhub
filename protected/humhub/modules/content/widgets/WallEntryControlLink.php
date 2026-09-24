<?php

namespace humhub\modules\content\widgets;

use humhub\components\Widget;
use humhub\helpers\Html;
use humhub\widgets\Icon;
use humhub\widgets\menu\MenuLink;
use ReflectionMethod;
use yii\helpers\ArrayHelper;

/**
 * Generic WallEntryControlLink.
 *
 * A widget that renders one `<li><a>` of a content's context menu - from the time menus were
 * widget stacks. The entries of the menu are menu entries now: extend
 * {@see \humhub\widgets\menu\MenuLink} instead, as core's own control links
 * ({@see DeleteLink}, {@see EditLink}, …) do. This class stays for modules extending it.
 *
 * @since 1.2
 * @deprecated since 1.20, extend {@see \humhub\widgets\menu\MenuLink} instead
 * @author buddh4
 */
class WallEntryControlLink extends Widget
{
    /**
     * @var string link label
     */
    public $label;

    /**
     * @var string link action
     */
    public $action;

    /**
     * @var string link action-url
     */
    public $actionUrl;

    /**
     * Object derived from ContentActiveRecord
     *
     * @var string
     */
    public $icon;

    /**
     *
     * @var [] link html options
     */
    public $options = [];

    /**
     * @inheritdoc
     */
    public function init()
    {
        if (empty($this->getLabel())) {
            $this->label = ArrayHelper::remove($this->options, 'label', 'Label');
        }

        if (!empty($this->getAction())) {
            $this->options['data-action-click'] = $this->getAction();
        }

        if (!empty($this->getActionUrl())) {
            $this->options['data-action-url'] = $this->getActionUrl();
        }

        $this->options['class'] = 'dropdown-item';

        ArrayHelper::remove($this->options, 'sortOrder');
        parent::init();
    }

    /**
     * @inheritdoc
     */
    public function run()
    {
        if ($this->preventRender()) {
            return '';
        }

        return '<li>' . $this->renderLink() . '</li>';
    }

    /**
     * This function may contain validation logic as permission checks.
     *
     * @return bool true if this link should be rendered false if not
     */
    public function preventRender()
    {
        return false;
    }

    /**
     * @return string renders the actual link
     */
    protected function renderLink()
    {
        return Html::a($this->renderLinkText(), '#', $this->options);
    }

    /**
     * @return string renders the link text with icon
     */
    protected function renderLinkText()
    {
        $label = trim($this->getLabel());
        return $this->icon ? Icon::get($this->getIcon()) . ' ' . $label : $label;
    }

    /**
     * @return string link label
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @return string link icon
     */
    public function getIcon()
    {
        if (empty($this->icon)) {
            $this->icon = ArrayHelper::remove($this->options, 'icon');
        }

        return $this->icon;
    }

    /**
     * @return string|null action url
     * @since 1.3
     */
    public function getActionUrl()
    {
        return null;
    }

    /**
     * @return string|null link action
     * @since 1.3
     */
    private function getAction()
    {
        return $this->action;
    }

    /**
     * The menu link this widget renders, for a client that renders the menu itself (see
     * {@see \humhub\modules\content\controllers\api\ControlsController}): label, icon and
     * html options, including the `data-action-click`/`data-action-url` pair {@see self::init()}
     * derived from `$action`/`$actionUrl`, which keeps a legacy action entry working when a
     * client renders the anchor instead of the server.
     *
     * **Only when this class' own `renderLink()` is in effect.** A subclass that overrides it
     * builds its markup from something other than these properties (`EditPageLink` of the wiki
     * renders a real href), so converting it here would produce an entry with an empty label
     * or a dead `#` link. Such a subclass, and one that prevents its own rendering, answers
     * null and is rendered instead.
     *
     * @since 1.20
     */
    public function toMenuLink(): ?MenuLink
    {
        if ($this->preventRender() || $this->rendersItsOwnLink()) {
            return null;
        }

        return new MenuLink([
            'label' => trim((string)$this->getLabel()),
            'icon' => $this->getIcon() ?: null,
            'url' => '#',
            'htmlOptions' => $this->options,
        ]);
    }

    /**
     * Whether a subclass took over link rendering, in which case this class cannot know what
     * the entry actually looks like.
     *
     * @since 1.20
     */
    protected function rendersItsOwnLink(): bool
    {
        return (new ReflectionMethod($this, 'renderLink'))->getDeclaringClass()->getName() !== self::class;
    }
}
