<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\admin\controllers;

use humhub\components\access\ControllerAccess;
use humhub\helpers\ThemeHelper;
use humhub\modules\admin\components\Controller;
use humhub\modules\admin\models\forms\AccessibilitySettingsForm;
use humhub\modules\admin\models\forms\AppearanceGeneralForm;
use humhub\modules\admin\models\forms\AppearanceImagesForm;
use humhub\modules\admin\models\forms\CustomScssForm;
use humhub\modules\admin\models\forms\DarkModeSettingsForm;
use humhub\modules\admin\models\forms\ThemeSettingsForm;
use humhub\modules\admin\permissions\ManageSettings;
use Yii;
use yii\base\Model;
use yii\web\NotFoundHttpException;

/**
 * Administration → Appearance
 *
 * @since 1.20
 */
class AppearanceController extends Controller
{
    /**
     * @inheritdoc
     */
    public $adminOnly = false;

    /**
     * Image of `Yii::$app->img` per delete action
     */
    private const DELETABLE_IMAGES = [
        'logo' => 'logo',
        'logo-dark' => 'logoDark',
        'icon' => 'icon',
        'login-background' => 'loginBackground',
        'mail-header' => 'mailHeader',
    ];

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->setActionTitles([
            'index' => Yii::t('AdminModule.base', 'Appearance'),
            'images' => Yii::t('AdminModule.base', 'Logo & Images'),
            'theme' => Yii::t('AdminModule.base', 'Theme'),
            'dark-mode' => Yii::t('AdminModule.base', 'Dark Mode'),
            'accessibility' => Yii::t('AdminModule.base', 'Accessibility'),
            'custom-scss' => Yii::t('AdminModule.base', 'Custom SCSS'),
        ]);
        $this->subLayout = '@admin/views/layouts/appearance';

        parent::init();
    }

    /**
     * @inheritdoc
     */
    protected function getAccessRules()
    {
        return [
            ['permissions' => ManageSettings::class],
            [ControllerAccess::RULE_POST => ['delete-image']],
        ];
    }

    public function actionIndex()
    {
        return $this->saveOrRender(new AppearanceGeneralForm(), 'index');
    }

    public function actionImages()
    {
        return $this->saveOrRender(new AppearanceImagesForm(), 'images');
    }

    public function actionTheme()
    {
        return $this->saveOrRender(new ThemeSettingsForm(), 'theme', true);
    }

    public function actionDarkMode()
    {
        return $this->saveOrRender(new DarkModeSettingsForm(), 'dark-mode');
    }

    public function actionAccessibility()
    {
        return $this->saveOrRender(new AccessibilitySettingsForm(), 'accessibility');
    }

    public function actionCustomScss()
    {
        return $this->saveOrRender(new CustomScssForm(), 'custom-scss', true);
    }

    /**
     * Deletes one of the images of [[DELETABLE_IMAGES]]
     */
    public function actionDeleteImage(string $image)
    {
        if (!isset(self::DELETABLE_IMAGES[$image])) {
            throw new NotFoundHttpException();
        }

        Yii::$app->img->{self::DELETABLE_IMAGES[$image]}->delete();

        return $this->asJson([]);
    }

    /**
     * @param bool $buildCss whether the theme CSS is rebuilt after saving
     */
    private function saveOrRender(Model $model, string $view, bool $buildCss = false)
    {
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            $result = $buildCss ? ThemeHelper::buildCss() : true;
            if ($result === true) {
                $this->view->saved();
            } else {
                $this->view->error($result);
            }

            // Full reload: the colour mode, accessibility options and the theme CSS apply on the next page load
            return $this->redirect([$this->action->id]);
        }

        return $this->render($view, ['model' => $model]);
    }
}
