<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2016 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\widgets\mails;

use humhub\modules\ui\mail\MailStyle;

/**
 * MailButton renders a button for email layouts/views.
 *
 * @author buddha
 * @since 1.2
 */
class MailButton extends \yii\base\Widget
{
    /**
     * @var string hex color, default is primary theme color
     */
    public $color;

    /**
     * @var string can be used instead of $color and accepts values as primary|info|success or any other theme variable etc.
     */
    public $type;

    /**
     * @var string target url
     */
    public $url;

    /**
     * @var string button text
     */
    public $text;

    /**
     * @inheritdoc
     */
    public function run()
    {
        if ($this->type) {
            $this->color = MailStyle::getVariable($this->text);
        }

        if (!$this->color) {
            $this->color = MailStyle::getColorPrimary();
        }

        return $this->render('mailButton', [
            'color' => $this->color,
            'url' => $this->url,
            'text' => $this->text,
        ]);
    }

}
