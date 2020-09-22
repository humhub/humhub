<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2020 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\libs;

use yii\httpclient\Client;
use yii\httpclient\CurlTransport;

/**
 * Class HttpClient
 *
 * @since 1.5
 * @package humhub\libs
 * @property $transport CurlTransport
 */
class HttpClient extends Client
{
    /**
     * @inheritDoc
     */
    public function init()
    {
        $this->transport = new CurlTransport();
        parent::init();
    }

    /**
     * @inheritDoc
     */
    public function beforeSend($request)
    {
        $request->setOptions(CURLHelper::getOptions());
        parent::beforeSend($request);
    }

}
