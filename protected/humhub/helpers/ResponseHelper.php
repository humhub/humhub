<?php

namespace humhub\helpers;

use humhub\modules\file\Module;
use Yii;

final class ResponseHelper
{
    public static function sendFile($filePath, $attachmentName = null, $options = []): void
    {
        /** @var Module $fileModule */
        $fileModule = Yii::$app->getModule('file');

        if (!$fileModule->settings->get('useXSendfile')) {
            Yii::$app->response->sendFile($filePath, $attachmentName, $options);
        } else {
            if (preg_match('/nginx|frankenphp/i', $_SERVER['SERVER_SOFTWARE'] ?? '')) {
                // set nginx specific X-Sendfile header name
                $options['xHeader'] = 'X-Accel-Redirect';
                // make path relative to docroot
                $docroot = rtrim((string)$_SERVER['DOCUMENT_ROOT'], DIRECTORY_SEPARATOR);
                if (str_starts_with($filePath, $docroot)) {
                    $filePath = substr($filePath, strlen($docroot));
                }
            }

            Yii::$app->response->xSendFile($filePath, $attachmentName, $options);
        }
    }
}
