<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2016 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\widgets\mails;

/**
 * MailButtonList renders multiple buttons for email layouts/views.
 *
 * @author buddha
 * @since 1.2
 */
class MailButtonList extends \yii\base\Widget
{

    /**
     * @var string hex color
     */
    public $buttons = [];


    /**
     * @inheritdoc
     */
    public function run()
    {
        return $this->render('mailButtonList', [
                    'buttons' => $this->buttons
        ]);
    }
}
