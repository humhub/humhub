<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\widgets;

use humhub\components\Widget;
use humhub\libs\Html;
use Yii;

/**
 * PoweredBy widget
 *
 * @since 1.3.7
 * @author Luke
 */
class PoweredBy extends Widget
{

    /**
     * @var bool return text link only
     */
    public $textOnly = false;

    /**
     * @var array link tag HTML options
     */
    public $linkOptions = [];

    /**
     * @inheritdoc
     */
    public function run()
    {

        if (isset(Yii::$app->params['hidePoweredBy'])) {
            return '';
        }

        if ($this->textOnly) {
            return Yii::t('base', 'Powered by {name}', ['name' => 'HumHub (https://humhub.org)']);
        }

        if (!isset($this->linkOptions['target'])) {
            $this->linkOptions['target'] = '_blank';
        }

        return Yii::t('base', 'Powered by {name}', [
            'name' => Html::a('HumHub', 'https://humhub.org', $this->linkOptions)
        ]);
    }

}
