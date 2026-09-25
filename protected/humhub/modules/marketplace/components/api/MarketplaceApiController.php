<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\marketplace\components\api;

use humhub\components\api\BaseController;
use humhub\modules\admin\permissions\ManageModules;
use humhub\modules\marketplace\components\OnlineModuleManager;
use humhub\modules\marketplace\Module;
use Yii;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;

/**
 * Base of the marketplace API controllers: session-callable for the platform's own island,
 * absent while the marketplace is disabled, and for module administrators only.
 *
 * @since 1.20
 */
abstract class MarketplaceApiController extends BaseController
{
    /**
     * @inheritdoc
     */
    protected bool $allowSessionAuth = true;

    /**
     * @inheritdoc
     */
    public function beforeAction($action)
    {
        if (!parent::beforeAction($action)) {
            return false;
        }

        if (!Module::isMarketplaceEnabled()) {
            throw new NotFoundHttpException(Yii::t('MarketplaceModule.base', 'Marketplace is disabled.'));
        }

        if (!Yii::$app->user->can(ManageModules::class)) {
            throw new ForbiddenHttpException();
        }

        return true;
    }

    protected function getMarketplaceModule(): Module
    {
        return Yii::$app->getModule('marketplace');
    }

    protected function getOnlineModuleManager(): OnlineModuleManager
    {
        return $this->getMarketplaceModule()->getOnlineModuleManager();
    }
}
