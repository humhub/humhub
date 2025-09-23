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

        $keyword = $this->getKeyword();
        if ($keyword !== null) {
            Yii::$app->view->registerJsConfig('content.highlight', ['keyword' => $keyword]);
        }
    }

    private function getKeyword(): ?string
    {
        if (!(Yii::$app instanceof Application && Yii::$app->isInstalled())) {
            return null;
        }

        $keyword = Yii::$app->session->get('contentHighlight');
        if ($keyword !== null && $keyword !== '') {
            Yii::$app->session->remove('contentHighlight');
            return $keyword;
        }

        $keyword = Yii::$app->request->get('highlight');
        if ($keyword !== null && $keyword !== '') {
            return $keyword;
        }

        if (isset(Yii::$app->request->referrer)
            && preg_match('/search.*?(&|\?)keyword=(.*?)(&|$)/i', Yii::$app->request->referrer, $m)
            && $m[2] !== '') {
            return urldecode($m[2]);
        }

        return null;
    }
}
