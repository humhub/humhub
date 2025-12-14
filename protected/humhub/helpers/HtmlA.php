<?php

namespace humhub\helpers;

use yii\helpers\Url;

class HtmlA implements \Stringable
{


    private string $text;
    private array $options;
    private bool $encodeLabel = false;

    public function __construct(string $text, mixed $url, array $options)
    {
        if ($url !== null) {
            $this->options['href'] = Url::to($url);
        }
        Html::addCssClass($options, ['class' => 'link']);

        $this->text = $text;
        $this->options = $options;
    }

    public function __toString()
    {
        $text = $this->encodeLabel ? Html::encode($this->text) : $this->text;
        return Html::tag('a', $text, $this->options);
    }

    protected function &getOptionsRef(): array
    {
        return $this->options;
    }

    public function post(): self
    {
        $this->options['data-method'] = 'POST';
        return $this;
    }

    public function target($target): self
    {
        $this->options['target'] = $target;
        return $this;
    }

    public function blank(): self
    {
        return $this->target('_blank');
    }
}
