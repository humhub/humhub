<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2020 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\marketplace\components;

use humhub\libs\HttpClient;
use Yii;

/**
 * HttpClient
 *
 * @since 1.5
 */
class HumHubApiClient extends HttpClient
{
    /**
     * @inheritDoc
     */
    public $baseUrl;

    /**
     * @inheritDoc
     */
    public function init()
    {
        if (empty($this->baseUrl)) {
            $this->baseUrl = Yii::$app->params['humhub']['apiUrl'];

        }

        parent::init();
    }

    /**
     * @inheritDoc
     */
    public function beforeSend($request)
    {
        $request->addData([
            'version' => Yii::$app->version,
            'installId' => Yii::$app->getModule('admin')->settings->get('installationId')
        ]);
        parent::beforeSend($request);
    }
}
