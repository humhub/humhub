<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\marketplace\controllers\api;

use humhub\modules\admin\libs\HumHubAPI;
use humhub\modules\marketplace\components\api\MarketplaceApiController;
use humhub\modules\marketplace\services\MarketplaceListService;
use humhub\modules\marketplace\services\MarketplaceService;
use Yii;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\web\HttpException;

/**
 * What the marketplace page needs besides the module list: the categories of its filter, the
 * notice about a newer HumHub version, its settings and the registration of a licence key.
 *
 * @since 1.20
 */
class MarketplaceController extends MarketplaceApiController
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return ArrayHelper::merge(parent::behaviors(), [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'categories' => ['GET', 'HEAD'],
                    'use-case' => ['GET', 'HEAD'],
                    'core-version' => ['GET', 'HEAD'],
                    'settings' => ['GET', 'HEAD'],
                    'update-settings' => ['PATCH'],
                    'licence-key' => ['POST'],
                ],
            ],
        ]);
    }

    /**
     * The categories with the number of listed modules in each; modules without a category
     * are the pseudo category `-1`, appended when there are any.
     */
    public function actionCategories()
    {
        $data = $this->getOnlineModuleManager()->getCategoryList();

        if ($data === null) {
            throw new HttpException(503, Yii::t('MarketplaceModule.base', 'Could not connect to HumHub API!'));
        }

        $results = $data['categories'];
        if ($data['uncategorized'] > 0) {
            $results[] = [
                'id' => -1,
                'name' => Yii::t('MarketplaceModule.base', 'Without category'),
                'count' => $data['uncategorized'],
            ];
        }

        return ['results' => $results];
    }

    /**
     * The use cases present among the listed modules, each with the number of modules having
     * it (`humhub.com`'s free-form `useCases` field, humanised — there is no translation
     * source for it).
     */
    public function actionUseCase()
    {
        $service = new MarketplaceListService($this->getOnlineModuleManager());
        if (!$service->isAvailable()) {
            throw new HttpException(503, Yii::t('MarketplaceModule.base', 'Could not connect to HumHub API!'));
        }

        return ['results' => $service->useCaseCounts()];
    }

    /**
     * The installed and the latest HumHub version. `latest` is `null` while humhub.com cannot
     * be reached — the page then shows no notice, as it never did.
     */
    public function actionCoreVersion()
    {
        $latest = HumHubAPI::getLatestHumHubVersion() ?: null;

        return [
            'installed' => Yii::$app->version,
            'latest' => $latest,
            'updateAvailable' => $latest !== null && version_compare($latest, Yii::$app->version, '>'),
            'updateUrl' => Yii::$app->hasModule('updater')
                ? Url::to(['/updater/update'], true)
                : 'https://docs.humhub.org/docs/admin/updating/',
        ];
    }

    public function actionSettings()
    {
        return (new MarketplaceService())->getPublicSettings();
    }

    /**
     * Partial update: a setting the body does not carry keeps its value.
     */
    public function actionUpdateSettings()
    {
        $values = [];
        $errors = [];

        foreach (MarketplaceService::SETTING_KEYS as $key) {
            $raw = Yii::$app->request->getBodyParam($key);
            if ($raw === null) {
                continue;
            }

            $value = filter_var($raw, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            if ($value === null) {
                $errors[$key] = [Yii::t('yii', '{attribute} must be either "{true}" or "{false}".', [
                    'attribute' => $key,
                    'true' => '1',
                    'false' => '0',
                ])];
                continue;
            }

            $values[$key] = $value;
        }

        if ($errors !== []) {
            return $this->validationErrors($errors);
        }

        return (new MarketplaceService())->updateSettings($values);
    }

    /**
     * Registers the licence key of a purchased module with humhub.com; the purchased module
     * then shows up in the list (`tag=purchased`), which is re-read here. Humhub.com being
     * unreachable answers `503`, same as the rest of this controller; an invalid key is a
     * `422` validation error.
     */
    public function actionLicenceKey()
    {
        $licenceKey = trim((string)Yii::$app->request->getBodyParam('licenceKey', ''));

        if ($licenceKey === '') {
            return $this->missingParameter('licenceKey');
        }

        $result = MarketplaceService::addLicenceKey($licenceKey);
        if ($result['hasError']) {
            if ($result['unreachable']) {
                throw new HttpException(503, $result['message']);
            }

            return $this->validationErrors(['licenceKey' => [$result['message']]]);
        }

        $this->getOnlineModuleManager()->getModules(false);

        return ['message' => $result['message']];
    }
}
