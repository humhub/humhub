<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\admin\controllers\api;

use humhub\components\api\BaseController;
use humhub\modules\admin\jobs\DisableModuleJob;
use humhub\modules\admin\jobs\RemoveModuleJob;
use humhub\modules\admin\permissions\ManageModules;
use humhub\modules\queue\helpers\QueueHelper;
use Yii;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\web\ServerErrorHttpException;

/**
 * Enabling an installed module, `POST module/<id>/enable` (see `docs/api/src/module.yaml`) -
 * an operation the caller triggers, hence a verb sub-path. Not marketplace-specific, hence
 * here; the marketplace island is its first consumer. The web routes `admin/module/enable|disable`
 * remain until the module administration becomes an island.
 *
 * @since 1.20
 */
class ModuleController extends BaseController
{
    /**
     * @inheritdoc
     */
    protected bool $allowSessionAuth = true;

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return ArrayHelper::merge(parent::behaviors(), [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'enable' => ['POST'],
                ],
            ],
        ]);
    }

    /**
     * @inheritdoc
     */
    public function beforeAction($action)
    {
        if (!parent::beforeAction($action)) {
            return false;
        }

        if (!Yii::$app->user->can(ManageModules::class)) {
            throw new ForbiddenHttpException();
        }

        return true;
    }

    /**
     * Enables the module. Enabling an enabled module answers its state (idempotent); a module
     * whose deactivation or removal is still queued answers `422`, as the web action refuses
     * it; a module that refuses to enable for any other reason answers `500`.
     */
    public function actionEnable($id)
    {
        $module = Yii::$app->moduleManager->getModule((string)$id, false);

        if ($module === null) {
            throw new NotFoundHttpException(Yii::t('AdminModule.modules', 'Could not find requested module!'));
        }

        if (!$module->getIsEnabled()) {
            if (QueueHelper::isQueued(new DisableModuleJob(['moduleId' => $module->id]))) {
                return $this->validationErrors(['id' => [Yii::t('AdminModule.modules', 'Deactivation of this module has not been completed yet. Please retry in a few minutes.')]]);
            }

            if (QueueHelper::isQueued(new RemoveModuleJob(['moduleId' => $module->id]))) {
                return $this->validationErrors(['id' => [Yii::t('AdminModule.modules', 'Uninstallation of this module has not been completed yet. It will be removed in a few minutes.')]]);
            }

            if ($module->enable() === false) {
                throw new ServerErrorHttpException(Yii::t('AdminModule.modules', 'Could not enable module!'));
            }
        }

        $configUrl = (string)$module->getConfigUrl();

        return [
            'id' => $module->id,
            'isEnabled' => true,
            'configUrl' => $configUrl !== '' ? $configUrl : null,
        ];
    }
}
