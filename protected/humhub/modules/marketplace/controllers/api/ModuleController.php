<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\marketplace\controllers\api;

use humhub\components\listing\ListContext;
use humhub\components\listing\ListValidationException;
use humhub\modules\marketplace\components\api\MarketplaceApiController;
use humhub\modules\marketplace\components\ModuleList;
use humhub\modules\marketplace\models\Module;
use humhub\modules\marketplace\serializers\MarketplaceModuleSerializer;
use humhub\modules\marketplace\services\MarketplaceListService;
use humhub\modules\marketplace\services\MarketplaceService;
use humhub\modules\marketplace\services\ModuleService;
use Yii;
use yii\data\Pagination;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\web\HttpException;
use yii\web\NotFoundHttpException;
use yii\web\UnprocessableEntityHttpException;

/**
 * The module list of the marketplace and the installation of a module (see
 * `docs/develop/concept-api.md` and `docs/api/src/marketplace.yaml`). Installing and updating
 * are operations the caller triggers, not states it sets, so each is a verb sub-path taking
 * `POST`: `marketplace/module/<id>/install`, `marketplace/module/<id>/update`.
 *
 * @since 1.20
 */
class ModuleController extends MarketplaceApiController
{
    public const DEFAULT_PAGE_SIZE = 24;

    public const MAX_PAGE_SIZE = 100;

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return ArrayHelper::merge(parent::behaviors(), [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'index' => ['GET', 'HEAD'],
                    'install' => ['POST'],
                    'update' => ['POST'],
                ],
            ],
        ]);
    }

    /**
     * One page of the modules the marketplace lists, built by {@see ModuleList}, which parses
     * and validates the query parameters (an invalid value or an unknown parameter answers
     * `422 {errors}`). The envelope carries `updateCount` — the available updates regardless of
     * the filter — for the "Update all" button, the way the notification list carries
     * `unseenCount`.
     */
    public function actionIndex()
    {
        $request = Yii::$app->request;
        $service = new MarketplaceListService($this->getOnlineModuleManager());

        try {
            $modules = array_values((new ModuleList($service))->build($this->listParams(), ListContext::forCurrentUser())->items());
        } catch (ListValidationException $e) {
            return $this->validationErrors($e->errors);
        }

        if (!$service->isAvailable()) {
            throw new HttpException(503, Yii::t('MarketplaceModule.base', 'Could not connect to HumHub API!'));
        }

        $pagination = new Pagination(['totalCount' => count($modules)]);
        $pagination->setPageSize(max(1, min((int)$request->get('pageSize', self::DEFAULT_PAGE_SIZE), self::MAX_PAGE_SIZE)));
        $pagination->setPage(max(1, (int)$request->get('page', 1)) - 1);

        $results = array_map(
            [MarketplaceModuleSerializer::class, 'module'],
            array_slice($modules, $pagination->offset, $pagination->limit),
        );

        $updateCount = $service->updateCount();

        // Written with the same count the cron (`RefreshPendingModuleUpdateCountJob`) uses —
        // `getModuleUpdates()`, not `$service->updateCount()` — so the badge does not flip
        // between two different counts depending on which of the two last wrote it.
        $marketplaceService = new MarketplaceService();
        $pendingModuleUpdateCount = count($this->getOnlineModuleManager()->getModuleUpdates());
        if ($pendingModuleUpdateCount !== $marketplaceService->getPendingModuleUpdateCount()) {
            $marketplaceService->refreshPendingModuleUpdateCount($pendingModuleUpdateCount);
        }

        return $this->returnPagination($pagination, $results) + ['updateCount' => $updateCount];
    }

    /**
     * Installs the latest compatible version. A module that is installed already answers its
     * state (idempotent); one that cannot be installed right away — to be bought, Professional
     * Edition only, no compatible version — answers `422`.
     */
    public function actionInstall($id)
    {
        $module = $this->findModule((string)$id);

        if (!$module->isInstalled()) {
            if ($module->getAvailability() !== Module::AVAILABILITY_INSTALL) {
                return $this->validationErrors([
                    'id' => [Yii::t('MarketplaceModule.base', 'This module cannot be installed.')],
                ]);
            }

            $this->getOnlineModuleManager()->install($module->id);
        }

        return MarketplaceModuleSerializer::module($this->findModule($module->id));
    }

    /**
     * Updates an installed module to its latest compatible version. Without an available
     * update the module's state is the answer (idempotent); a module that is not installed,
     * or whose download humhub.com refuses (licence expired), answers `422`.
     */
    public function actionUpdate($id)
    {
        $module = $this->findModule((string)$id);

        if (!$module->isInstalled()) {
            return $this->validationErrors([
                'id' => [Yii::t('MarketplaceModule.base', 'This module is not installed.')],
            ]);
        }

        if ($module->isUpdateAvailable()) {
            try {
                (new ModuleService($module->id))->update();
            } catch (UnprocessableEntityHttpException $e) {
                return $this->validationErrors(['id' => [$e->getMessage()]]);
            }
        }

        return MarketplaceModuleSerializer::module($this->findModule($module->id));
    }

    /**
     * @throws NotFoundHttpException for a module the marketplace does not list
     */
    private function findModule(string $id): Module
    {
        $modules = (new MarketplaceListService($this->getOnlineModuleManager()))->all();

        if (!isset($modules[$id])) {
            throw new NotFoundHttpException(Yii::t('MarketplaceModule.base', 'Could not find the requested module!'));
        }

        return $modules[$id];
    }
}
