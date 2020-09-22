<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2016 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\widgets\mails;

use Yii;

/**
 * Simple headline for mails.
 *
 * @author buddha
 * @since 1.2
 */
class MailHeadline extends \yii\base\Widget
{
    /**
     * @var string button text
     */
    public $text;
    
    /**
     * @var int headline level 1-3
     */
    public $level;
    
    /**
     * @var string optional additional text style
     */
    public $style;

    /**
     * @inheritdoc
     */
    public function run()
    {
        if(!$this->level) {
            $this->level = 1;
        }
        
        return $this->render('mailHeadline', [
                    'text' => $this->text,
                    'level' => $this->level,
                    'style' => $this->style
        ]);
    }

}
