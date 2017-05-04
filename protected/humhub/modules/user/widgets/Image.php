<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\widgets;

use Yii;
use humhub\libs\Html;
use humhub\components\Widget;

/**
 * Image shows the user profile image
 *
 * @since 1.2
 * @author Luke
 */
class Image extends Widget
{

    /**
     * @var \humhub\modules\user\models\User
     */
    public $user;

    /**
     * @var int the image width in pixcel
     */
    public $width = 50;

    /**
     * @var int the image height in pixel (optional)
     */
    public $height = null;

    /**
     * @var boolean add link to user profile
     */
    public $link = true;

    /**
     * @var array optional html options for the link tag
     */
    public $linkOptions = [];

    /**
     * @var array optional html options for the base tag
     */
    public $htmlOptions = [];

    /**
     * @var array optional html options for the image tag
     */
    public $imageOptions = [];

    /**
     * @var string show tooltip with further information about the user
     */
    public $showTooltip = false;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        if ($this->height === null) {
            $this->height = $this->width;
        }
    }

    /**
     * @inheritdoc
     */
    public function run()
    {
        Html::addCssClass($this->imageOptions, 'img-rounded');
        Html::addCssStyle($this->imageOptions, 'width: ' . $this->width . 'px; height: ' . $this->height . 'px');
        
        if ($this->showTooltip) {
            $this->imageOptions['data-toggle'] = 'tooltip';
            $this->imageOptions['data-placement'] = 'top';
            $this->imageOptions['data-original-title'] = Html::encode($this->user->displayName);
            Html::addCssClass($this->imageOptions, 'tt');
        }

        $this->imageOptions['alt'] = Yii::t('base', 'Profile picture of {displayName}', ['displayName' => Html::encode($this->user->displayName)]);
        $html = Html::img($this->user->getProfileImage()->getUrl(), $this->imageOptions);

        if ($this->link) {
            $html = Html::a($html, $this->user->getUrl(), $this->linkOptions);
        }

        $html = Html::tag('span', $html, $this->htmlOptions);

        return $html;
    }

}
