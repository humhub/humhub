<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\components;

use Yii;
use yii\web\Controller;
use yii\web\Request;

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

        $this->on(self::EVENT_BEFORE_SEND, function () {
            $this->addCsrfTokenHeader();
        });
    }

    /**
     * Provides the current CSRF token to AJAX requests, so that pages which are open for a longer time
     * can replace an outdated token, e.g. after a re-login in another browser tab.
     */
    private function addCsrfTokenHeader(): void
    {
        $request = Yii::$app->request;
        $controller = Yii::$app->controller;

        if (
            $request instanceof Request
            && $request->enableCsrfValidation
            && $request->getIsAjax()
            && $controller instanceof Controller
            && $controller->enableCsrfValidation
        ) {
            $this->headers->set(Request::CSRF_HEADER, $request->getCsrfToken());
        }
    }

    /**
     * @inheritdoc
     */
    public function xSendFile($filePath, $attachmentName = null, $options = [])
    {
        if (preg_match('/nginx|frankenphp/i', $_SERVER['SERVER_SOFTWARE'] ?? '')) {
            // set nginx specific X-Sendfile header name
            $options['xHeader'] = 'X-Accel-Redirect';
            // make path relative to docroot
            $docroot = rtrim((string) $_SERVER['DOCUMENT_ROOT'], DIRECTORY_SEPARATOR);
            if (str_starts_with($filePath, $docroot)) {
                $filePath = substr($filePath, strlen($docroot));
            }
        }

        return parent::xSendFile($filePath, $attachmentName, $options);
    }
}
