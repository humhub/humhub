<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\marketplace\controllers\api;

use humhub\modules\marketplace\components\api\MarketplaceApiController;
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
     * One page of the modules the marketplace lists, filtered by the query parameters. The
     * envelope carries `updateCount` — the available updates regardless of the filter — for
     * the "Update all" button, the way the notification list carries `unseenCount`.
     */
    public function actionIndex()
    {
        $request = Yii::$app->request;
        $errors = [];

        $params = [
            'q' => is_string($request->get('q')) ? $request->get('q') : '',
            'categoryId' => $this->categoryIdParam($errors),
            'status' => $this->listParam('status', MarketplaceListService::STATUSES, $errors),
            'tag' => $this->listParam('tag', MarketplaceListService::TAGS, $errors),
            'useCase' => $this->useCaseParam($errors),
            'id' => is_string($request->get('id')) ? $request->get('id') : '',
        ];

        if ($errors !== []) {
            return $this->validationErrors($errors);
        }

        $service = new MarketplaceListService($this->getOnlineModuleManager());
        if (!$service->isAvailable()) {
            throw new HttpException(503, Yii::t('MarketplaceModule.base', 'Could not connect to HumHub API!'));
        }

        $modules = $service->find($params);

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
     * `categoryId` as the list expects it: absent or empty means "all", `-1` means "without
     * category", anything else must be an integer — collected into `$errors` otherwise.
     */
    private function categoryIdParam(array &$errors): ?int
    {
        $raw = Yii::$app->request->get('categoryId');

        if ($raw === null || $raw === '') {
            return null;
        }

        $value = filter_var($raw, FILTER_VALIDATE_INT);
        if ($value === false) {
            $errors['categoryId'][] = Yii::t('yii', '{attribute} must be an integer.', ['attribute' => 'categoryId']);

            return null;
        }

        return $value;
    }

    /**
     * A list parameter, repeated (`status[]=a&status[]=b`) or comma-separated (`status=a,b`).
     * Unknown values are collected into `$errors` under the parameter's name.
     *
     * @return string[]
     */
    private function listParam(string $name, array $allowed, array &$errors): array
    {
        $values = $this->splitListParam($name);

        foreach ($values as $value) {
            if (!in_array($value, $allowed, true)) {
                $errors[$name][] = Yii::t('MarketplaceModule.base', 'Unknown value "{value}".', ['value' => $value]);
            }
        }

        return $values;
    }

    /**
     * Like {@see self::listParam()}, but humhub.com is free to add use cases without a core
     * release, so any lowercase id is accepted instead of a fixed list of known values.
     *
     * @return string[]
     */
    private function useCaseParam(array &$errors): array
    {
        $values = $this->splitListParam('useCase');

        foreach ($values as $value) {
            if (!preg_match('/^[a-z0-9_-]+$/', $value)) {
                $errors['useCase'][] = Yii::t('MarketplaceModule.base', 'Unknown value "{value}".', ['value' => $value]);
            }
        }

        return $values;
    }

    /**
     * @return string[] repeated (`name[]=a&name[]=b`) or comma-separated (`name=a,b`) values,
     *         trimmed and with empty ones dropped
     */
    private function splitListParam(string $name): array
    {
        $raw = Yii::$app->request->get($name, []);
        $values = is_array($raw) ? $raw : explode(',', (string)$raw);

        return array_values(array_filter(
            array_map(static fn($value) => is_scalar($value) ? trim((string)$value) : '', $values),
            static fn(string $value) => $value !== '',
        ));
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
