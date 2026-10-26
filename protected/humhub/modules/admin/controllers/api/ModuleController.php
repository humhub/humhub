<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\admin\controllers\api;

use humhub\components\api\DataTableController;
use humhub\components\table\DataTable;
use humhub\modules\admin\components\ModuleTable;
use humhub\modules\admin\jobs\DisableModuleJob;
use humhub\modules\admin\jobs\RemoveModuleJob;
use humhub\modules\admin\permissions\ManageModules;
use humhub\modules\queue\helpers\QueueHelper;
use Yii;
use yii\helpers\ArrayHelper;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\web\ServerErrorHttpException;

/**
 * The installed modules (see `docs/api/src/admin.yaml` and `docs/api/src/module.yaml`):
 * `GET admin/module` answers the rows of the administration's module table ({@see ModuleTable},
 * the modules of `AdminModuleList`, its filters and sorts as query parameters) in the paginated
 * envelope, for those who may manage modules or settings; `POST module/<id>/enable` enables a
 * module (an operation the caller triggers, hence a verb sub-path), for those who may manage
 * modules. The marketplace island and the module table use it; disabling and uninstalling remain
 * the web routes `admin/module/disable|remove`, which queue a job.
 *
 * @since 1.20
 */
class ModuleController extends DataTableController
{
    /**
     * @inheritdoc
     */
    protected function createTable(): DataTable
    {
        return Yii::createObject(ModuleTable::class);
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return ArrayHelper::merge(parent::behaviors(), [
            'verbs' => [
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

        if ($action->id === 'enable' && !Yii::$app->user->can(ManageModules::class)) {
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
