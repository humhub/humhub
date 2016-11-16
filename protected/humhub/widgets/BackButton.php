<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\widgets;

use Yii;

/**
 * @inheritdoc
 */
class BackButton extends \yii\base\Widget
{
    public $text;
    public $route;
    public $url;
    public $clearFix = false;
    
    /**
     * @inheritdoc
     */
    public function run()
    {
        if($this->route !== null) {
            $this->url = \yii\helpers\Url::to([$this->route]);
        }
        
        if(!$this->text) {
            $this->text = Yii::t('base', 'Back');
        }
        
        $result = '<a href="'.$this->url.'" class="btn btn-default pull-right" data-ui-loader><i class="fa fa-arrow-left aria-hidden="true"></i> '.$this->text.'</a>';
        
        if($this->clearFix) {
            $result = '<div class="clearfix">'.$result.'</div>';
        }
        
        return $result;
    }

}
