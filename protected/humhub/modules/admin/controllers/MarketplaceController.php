<?php
/**
 * @link https://coinsence.org/
 * @copyright Copyright (c) 2020 Coinsence
 * @license https://www.humhub.com/licences
 *
 * @author Daly Ghaith <daly.ghaith@gmail.com>
 */

namespace humhub\modules\admin\controllers;

use humhub\modules\admin\permissions\ManageMarketplaces;
use humhub\modules\xcoin\models\Marketplace;
use Yii;
use humhub\modules\admin\components\Controller;
use yii\data\ActiveDataProvider;
use yii\web\ForbiddenHttpException;
use yii\web\HttpException;

/**
 * MarketplaceController provides management for spaces marketplaces.
 */
class MarketplaceController extends Controller
{

    /**
     * @inheritdoc
     */
    public $adminOnly = false;

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->subLayout = '@admin/views/layouts/marketplace';
        $this->appendPageTitle(Yii::t('AdminModule.base', 'Marketplaces'));

        return parent::init();
    }

    /**
     * @inheritdoc
     */
    public function getAccessRules()
    {
        return [
            ['permissions' => [ManageMarketplaces::class]]
        ];
    }

    /**
     * Shows all available marketplaces
     * @throws ForbiddenHttpException
     */
    public function actionIndex()
    {
        if (!Yii::$app->user->can(new ManageMarketplaces())) {
            return $this->forbidden();
        }


        $dataProvider = new ActiveDataProvider([
            'query' => Marketplace::find()
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Edit marketplace
     * @throws ForbiddenHttpException
     * @throws HttpException
     */
    public function actionEdit()
    {
        if (!Yii::$app->user->can(new ManageMarketplaces())) {
            return $this->forbidden();
        }

        $model = Marketplace::findOne(['id' => Yii::$app->request->get('id')]);

        if ($model == null) {
            throw new HttpException(404, Yii::t('AdminModule.controllers_MarketplaceController', 'Marketplace not found!'));
        }

        $model->scenario = Marketplace::SCENARIO_EDIT_ADMIN;

        if ($model->load(Yii::$app->request->post()) && $model->save()) {

            $this->view->saved();

            return $this->htmlRedirect(['index']);
        }

        return $this->renderAjax('edit', ['model' => $model]);
    }
}
