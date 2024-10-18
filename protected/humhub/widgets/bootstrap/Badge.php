<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\widgets\bootstrap;

use humhub\helpers\Html;
use yii\base\InvalidConfigException;
use yii\bootstrap5\Widget;

/**
 * Badge renders a bootstrap badge component.
 *
 * Usage example: `Badge::danger('New')->icon('user')->right()`.
 *
 * @since 1.18
 * @see https://getbootstrap.com/docs/5.3/components/badge/
 */
class Badge extends Widget
{
    use BootstrapVariationsTrait;

    /**
     * @var string|null the label of the badge.
     */
    public ?string $label = null;

    /**
     * @var bool whether to encode the label.
     */
    public bool $encodeLabel = false;

    public int $sortOrder = 1000;
    public $link;

    /**
     * Initializes the widget.
     * If you override this method, make sure you call the parent implementation first.
     * @throws InvalidConfigException
     */
    public function init()
    {
        parent::init();
        $this->clientOptions = [];
        Html::addCssClass($this->options, ['widget' => 'label']);
    }

    /**
     * {@inheritdoc}
     */
    public function run(): string
    {
        $text =
            ($this->icon ? $this->icon . ' ' : '') .
            ($this->encodeLabel ? Html::encode($this->label) : $this->label);

        $result = Html::tag('span', $text, $this->options);

        if ($this->link) {
            $result = (string) $this->link->setText($result);
        }

        return $result;
    }

    /**
     * Adds a pill variation to the badge.
     * @return $this
     */
    public function pill(): static
    {
        $this->cssClass(['rounded' => 'rounded-pill']);

        return $this;
    }

    /**
     * @inerhitdoc
     * @throws \Throwable
     */
    public static function instance(?string $text = null, ?string $color = null): static
    {
        return new static([
            'label' => $text,
            'options' => $color ? ['class' => ['text-bg-' . $color]] : [],
        ]);
    }

    public function sortOrder($sortOrder): static
    {
        $this->sortOrder = $sortOrder;
        return $this;
    }

    public static function sort(&$labels)
    {
        usort($labels, function ($a, $b) {
            if ($a->sortOrder == $b->sortOrder) {
                return 0;
            }

            if ($a->sortOrder < $b->sortOrder) {
                return - 1;
            }

            return 1;
        });

        return $labels;
    }

    /**
     * Adds a data-action-click handler to the button.
     * @param $handler
     * @param null $url
     * @param null $target
     * @return static
     */
    public function action($handler, $url = null, $target = null)
    {
        $this->link = Link::withAction($this->getText(), $handler, $url, $target);
        return $this;
    }

    public function withLink($link)
    {
        if ($link instanceof Link) {
            $this->link = $link;
        }

        return $this;
    }
}
