<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\components;

/**
 * Response
 *
 * @author Luke
 */
class Response extends \yii\web\Response
{
    public function init()
    {
        if (defined('YII_ENV_TEST') && YII_ENV_TEST && class_exists('indexTextResponseCode', false)) {
            \indexTextResponseCode::$response = $this;
        }

        parent::init();
    }

    /**
     * @inheritdoc
     */
    public function xSendFile($filePath, $attachmentName = null, $options = [])
    {
        if (isset($_SERVER['SERVER_SOFTWARE']) && stripos($_SERVER['SERVER_SOFTWARE'], 'nginx') === 0) {
            // set nginx specific X-Sendfile header name
            $options['xHeader'] = 'X-Accel-Redirect';
            // make path relative to docroot
            $docroot = rtrim($_SERVER['DOCUMENT_ROOT'], DIRECTORY_SEPARATOR);
            if (substr($filePath, 0, strlen($docroot)) == $docroot) {
                $filePath = substr($filePath, strlen($docroot));
            }
        }

        return parent::xSendFile($filePath, $attachmentName, $options);
    }
}
