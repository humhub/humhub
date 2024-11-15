<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\live\assets;

use humhub\components\assets\AssetBundle;
use humhub\modules\web\security\helpers\Security;
use Yii;

class LiveAsset extends AssetBundle
{
    /**
     * @inheritdoc
     */
    public $sourcePath = '@live/resources';

    /**
     * @inheritdoc
     */
    public $js = [
        'js/humhub.live.js',
        'js/humhub.live.poll.js',
    ];

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        if (!Yii::$app->request->isConsoleRequest) {
            Yii::$app->view->registerJsConfig('live.poll', [
                'nonce' => Security::getNonce(true),
            ]);
        }
    }
}
