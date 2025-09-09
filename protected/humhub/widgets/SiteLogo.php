<?php

namespace humhub\widgets;

class SiteLogo extends \yii\base\Widget
{
    public const PLACE_TOP_MENU = 'topMenu';
    public const PLACE_LOGIN = 'login';
    public const PLACE_EMAIL = 'email';

    public string $place = 'topMenu';
    public ?int $maxWidth = null;
    public ?int $maxHeight = null;
    public string $id = 'img-logo';
    public string $class = 'rounded';
    public string $style = '';

    public function init()
    {
        parent::init();

        if ($this->place === static::PLACE_LOGIN) {
            $this->maxWidth = $this->maxWidth ?? 500;
            $this->maxHeight = $this->maxHeight ?? 250;
        }
        if ($this->place === static::PLACE_EMAIL) {
            $this->maxWidth = $this->maxWidth ?? 600;
            $this->maxHeight = $this->maxHeight ?? 50;
        }
    }

    public function run()
    {
        return $this->render('logo', [
            'place' => $this->place,
            'maxWidth' => $this->maxWidth,
            'maxHeight' => $this->maxHeight,
            'id' => $this->id,
            'class' => $this->class,
            'style' => $this->style,
        ]);
    }

}
