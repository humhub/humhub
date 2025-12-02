<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\controllers;

use humhub\components\access\ControllerAccess;
use humhub\components\Controller;
use humhub\modules\user\helpers\AuthHelper;
use \Throwable;
use Yii;
use yii\base\UserException;
use yii\helpers\Url;
use yii\web\HttpException;

class ErrorController extends Controller
{

    public $access = ControllerAccess::class;

    private ?Throwable $exception;

    private ?string $buttonLabel;
    private ?string $buttonHref;

    public function beforeAction($action)
    {
        $this->exception = Yii::$app->getErrorHandler()->exception;
        if ($this->exception === null) {
            return false;
        }

        // Fix: https://github.com/humhub/humhub/issues/3848
        Yii::$app->view->theme->register();

        if (Yii::$app->user->isGuest && !AuthHelper::isGuestAccessEnabled()) {
            $this->layout = '@user/views/layouts/main';
            $this->buttonHref = Url::to(['/user/auth/login']);
            $this->buttonLabel = Yii::t('base', 'Back to login');
        } else {
            $this->buttonHref = Url::home();
            $this->buttonLabel = Yii::t('base', 'Back to dashboard');
        }

        return parent::beforeAction($action);
    }

    public function actionIndex()
    {
        if (Yii::$app->request->isAjax) {
            return $this->asJson([
                'error' => true,
                'message' => $this->getErrorMessage(),
            ]);
        }

        // Render special "login required" view for guests
        if (Yii::$app->user->isGuest && $this->exception instanceof HttpException &&
            $this->exception->statusCode == '401' && AuthHelper::isGuestAccessEnabled()) {
            Yii::$app->user->setReturnUrl(Yii::$app->request->getAbsoluteUrl());

            return $this->render('@humhub/views/error/401_guests', [
                'message' => $this->getErrorMessage(),
                'buttonLabel' => $this->buttonLabel,
                'buttonHref' => $this->buttonHref,
            ]);
        }

        return $this->render('@humhub/views/error/index', [
            'message' => $this->getErrorMessage(),
            'buttonLabel' => $this->buttonLabel,
            'buttonHref' => $this->buttonHref,
        ]);
    }

    private function getErrorMessage(): string
    {
        if ($this->exception instanceof UserException && !empty($this->exception->getMessage())) {
            return $this->exception->getMessage();
        }

        return Yii::t('error', 'An internal server error occurred.');
    }
}
