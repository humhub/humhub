<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\assets;

use humhub\components\assets\AssetBundle;
use Yii;

/**
 * TimeAgo Asset Locale
 *
 * @since 1.2
 * @author luke
 */
class JqueryTimeAgoLocaleAsset extends AssetBundle
{
    /**
     * @inheritdoc
     */
    public $defer = false;

    /**
     * @inheritdoc
     */
    public $sourcePath = '@npm/timeago';

    /**
     * @var array language mapping between humhub locale id and timeago messages
     */
    public $languageMapping = [
        'nb_no' => 'no',
        'pt-BR' => 'pt-br',
        'fa-IR' => 'fa',
    ];

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        $this->registerLocale();
    }

    /**
     * Adds the correct locale file to js files
     */
    protected function registerLocale()
    {
        $languageCode = Yii::$app->language;

        if (array_key_exists($languageCode, $this->languageMapping)) {
            $languageCode = $this->languageMapping[$languageCode];
        }

        $localeFile = 'locales' . DIRECTORY_SEPARATOR . 'jquery.timeago.' . $languageCode . '.js';
        if (file_exists(Yii::getAlias($this->sourcePath . '/' . $localeFile))) {
            $this->js[] = $localeFile;
        }
    }

}
