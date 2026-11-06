<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\controllers;

use humhub\modules\user\components\BaseAccountController;
use humhub\modules\user\models\forms\AccessibilitySettings;
use humhub\modules\user\models\forms\AppearanceSettings;
use Yii;
use yii\base\Model;

/**
 * Appearance & Accessibility settings of the current user's account
 *
 * @since 1.20
 */
class AppearanceController extends BaseAccountController
{
    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->setActionTitles([
            'index' => Yii::t('UserModule.account', 'Appearance'),
            'accessibility' => Yii::t('UserModule.account', 'Accessibility'),
        ]);

        parent::init();
    }

    public function actionIndex()
    {
        return $this->saveOrRender(new AppearanceSettings(['user' => $this->getUser()]), 'index');
    }

    public function actionAccessibility()
    {
        return $this->saveOrRender(new AccessibilitySettings(['user' => $this->getUser()]), 'accessibility');
    }

    private function saveOrRender(Model $model, string $view)
    {
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            $this->view->saved();

            // Full reload: the options apply on the next page load
            return $this->redirect([$this->action->id]);
        }

        return $this->render($view, ['model' => $model]);
    }
}
