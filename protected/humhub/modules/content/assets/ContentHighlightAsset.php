<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\content\assets;

use humhub\components\assets\AssetBundle;
use Yii;
use yii\web\Application;

class ContentHighlightAsset extends AssetBundle
{
    /**
     * @inheritdoc
     */
    public $sourcePath = '@content/resources';

    /**
     * @inheritdoc
     */
    public $js = [
        'js/humhub.content.highlight.js',
    ];

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        if (Yii::$app instanceof Application && Yii::$app->isInstalled()) {
            $highlight = Yii::$app->session->get('contentHighlight');
            if ($highlight !== null && $highlight !== '') {
                Yii::$app->session->remove('contentHighlight');
                Yii::$app->view->registerJsConfig('content.highlight', ['keyword' => $highlight]);
            }
        }
    }
}
