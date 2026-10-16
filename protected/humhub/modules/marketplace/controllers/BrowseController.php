<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2019 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\marketplace\controllers;

use humhub\modules\admin\components\Controller;
use humhub\modules\admin\permissions\ManageModules;
use humhub\modules\marketplace\Module;
use Yii;
use yii\web\NotFoundHttpException;

/**
 * The marketplace page; everything it does goes through `/api/v2/marketplace` (see `controllers\api\`).
 *
 * @property Module $module
 * @package humhub\modules\marketplace\controllers
 */
class BrowseController extends Controller
{
    /**
     * @inheritdoc
     */
    protected function getAccessRules()
    {
        return [
            ['permissions' => ManageModules::class],
        ];
    }

    /**
     * @inheritdoc
     */
    public function beforeAction($action)
    {
        if (!Module::isMarketplaceEnabled()) {
            throw new NotFoundHttpException(Yii::t('MarketplaceModule.base', 'Marketplace is disabled.'));
        }

        return parent::beforeAction($action);
    }

    public function actionIndex()
    {
        // The meta search links its "all results" to a provider's route with `keyword`, the
        // parameter every directory page used to read; the marketplace filters by `q`.
        $keyword = Yii::$app->request->get('keyword');
        if (is_string($keyword) && $keyword !== '') {
            $params = Yii::$app->request->get();
            unset($params['keyword']);
            $params['q'] ??= $keyword;

            return $this->redirect(array_merge(['/marketplace/browse'], $params));
        }

        $this->subLayout = '@admin/views/layouts/module';
        return $this->render('index');
    }
}
