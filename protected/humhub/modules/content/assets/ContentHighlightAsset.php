<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\content\assets;

use humhub\components\assets\AssetBundle;
use Yii;

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
    public static function register($view)
    {
        if (!Yii::$app->request->isConsoleRequest) {
            $highlight = Yii::$app->session->get('contentHighlight');
            if ($highlight !== null && $highlight !== '') {
                Yii::$app->session->remove('contentHighlight');
                $view->registerJsConfig('content.highlight', ['keyword' => $highlight]);
            }
        }

        return parent::register($view);
    }
}
