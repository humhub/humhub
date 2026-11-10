<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\controllers;

use humhub\components\access\ControllerAccess;
use humhub\components\Controller;
use humhub\modules\user\components\SudoMode;
use humhub\modules\user\models\forms\SudoConfirmForm;
use Yii;
use yii\helpers\Url;

/**
 * The confirmation of the sudo mode (docs/develop/concept-sudo-mode.md): the dialog for AJAX requests, the page
 * for a full page request, and the fresh login for users without a password factor.
 *
 * @since 1.20
 */
class SudoController extends Controller
{
    /**
     * @inheritdoc
     */
    protected function getAccessRules()
    {
        return [
            [ControllerAccess::RULE_LOGGED_IN_ONLY],
            [ControllerAccess::RULE_POST => ['relogin']],
        ];
    }

    /**
     * Asks for the password and, once it is right, continues: an AJAX request gets `{"success": true}`, a page
     * request is redirected to the return URL.
     */
    public function actionConfirm(?string $returnUrl = null)
    {
        $sudo = SudoMode::forCurrentUser();
        $returnUrl = $this->sanitizeReturnUrl($returnUrl);
        $model = new SudoConfirmForm($sudo);
        $request = Yii::$app->request;
        $params = ['model' => $model, 'returnUrl' => $returnUrl];

        if ($model->load($request->post())) {
            $confirmed = $model->validate();

            if ($request->getIsAjax()) {
                return $this->asJson($confirmed
                    ? ['success' => true]
                    : ['success' => false, 'output' => $this->renderAjax('confirm-modal', $params)]);
            }

            if ($confirmed) {
                return $this->redirect($returnUrl);
            }
        }

        return $request->getIsAjax()
            ? $this->renderAjax('confirm-modal', $params)
            : $this->render('confirm', $params);
    }

    /**
     * Signs the user in again through their current auth client: the session ends locally (not through the logout
     * action, so no Single Logout reaches the identity provider) and the login starts with the return URL.
     */
    public function actionRelogin(?string $returnUrl = null)
    {
        $returnUrl = $this->sanitizeReturnUrl($returnUrl);
        $route = SudoMode::forCurrentUser()->getReloginRoute();

        Yii::$app->user->logout();
        Yii::$app->user->setReturnUrl($returnUrl);

        return $this->redirect($route);
    }

    /**
     * @return string the return URL when it stays on this site, else the home page
     */
    private function sanitizeReturnUrl(?string $returnUrl): string
    {
        if ($returnUrl === null || $returnUrl === '' || !Url::isRelative($returnUrl) || str_starts_with($returnUrl, '//')) {
            return Url::home();
        }

        return $returnUrl;
    }
}
