<?php
/**
 * @link https://coinsence.org/
 * @copyright Copyright (c) 2018 Coinsence
 * @license https://www.humhub.com/licences
 *
 * @author Daly Ghaith <daly.ghaith@gmail.com>
 */

namespace humhub\modules\admin\controllers;

use humhub\modules\xcoin\models\Category;
use Throwable;
use Yii;
use humhub\modules\admin\components\Controller;
use humhub\modules\admin\permissions\ManageCategories;
use yii\data\ActiveDataProvider;
use yii\db\StaleObjectException;
use yii\web\ForbiddenHttpException;
use yii\web\HttpException;
use yii\web\Response;

/**
 * CategoryController provides global space administration.
 */
class CategoryController extends Controller
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
        $this->subLayout = '@admin/views/layouts/category';
        $this->appendPageTitle(Yii::t('AdminModule.base', 'Categories'));

        return parent::init();
    }

    /**
     * @inheritdoc
     */
    public function getAccessRules()
    {
        return [
            ['permissions' => [ManageCategories::class]]
        ];
    }

    /**
     * Shows all available categories
     * @throws ForbiddenHttpException
     */
    public function actionIndexFunding()
    {
        if (!Yii::$app->user->can(new ManageCategories())) {
            return $this->forbidden();
        }


        $dataProvider = new ActiveDataProvider([
            'query' => Category::find()->where(['type' => Category::TYPE_FUNDING])
        ]);

        return $this->render('index-funding', [
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Shows all available categories
     * @throws ForbiddenHttpException
     */
    public function actionIndexMarketplace()
    {
        if (!Yii::$app->user->can(new ManageCategories())) {
            return $this->forbidden();
        }

        $categories = [];

        return $this->render('index-marketplace', [
            'fundingCategories' => $categories,
        ]);
    }

    /**
     * Shows all available categories
     * @throws ForbiddenHttpException
     */
    public function actionCreate()
    {
        if (!Yii::$app->user->can(new ManageCategories())) {
            return $this->forbidden();
        }

        $model = new Category();
        $model->scenario = Category::SCENARIO_CREATE;

        if ($model->load(Yii::$app->request->post()) && $model->save()) {

            $model->fileManager->attach(Yii::$app->request->post('fileList'));
            $this->view->saved();

            return $this->htmlRedirect(['index-' . ($model->type == Category::TYPE_FUNDING ? 'funding' : 'marketplace')]);
        }

        return $this->renderAjax('create', ['model' => $model]);
    }

    /**
     * Delete category
     * @param $id
     * @return Response
     * @throws HttpException
     * @throws Throwable
     * @throws StaleObjectException
     */
    public function actionDelete($id)
    {
        $category = Category::findOne(['id' => $id]);
        if ($category === null) {
            throw new HttpException(404);
        }

        if($category->getFundings()->count()){
            $this->view->error(Yii::t('AdminModule.category', 'Can\'t delete a category that has crowdfunding campaigns'));
        } else {
            $category->delete();
            $this->view->success(Yii::t('AdminModule.category', 'Category successfully deleted'));
        }

        return $this->redirect(['index-' . ($category->type == Category::TYPE_FUNDING ? 'funding' : 'marketplace')]);
    }
}
