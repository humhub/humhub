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
    public function beforeRun()
    {
        return parent::beforeRun() && !self::isHidden();
    }

    /**
     * @inheritdoc
     */
    public function run()
    {
        return Yii::t('base', 'Powered by {name}', ['name' => $this->getHumHubName()]);
    }

    public static function isHidden(): bool
    {
        return !empty(Yii::$app->params['hidePoweredBy']);
    }

    protected function getHumHubName(): string
    {
        if ($this->textOnly) {
            return 'HumHub (https://www.humhub.com)';
        }

        if (!isset($this->linkOptions['target'])) {
            $this->linkOptions['target'] = '_blank';
        }

        return Html::a('HumHub', 'https://www.humhub.com', $this->linkOptions);
    }

}
