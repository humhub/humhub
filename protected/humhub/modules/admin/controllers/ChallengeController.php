<?php
/**
 * @link https://coinsence.org/
 * @copyright Copyright (c) 2018 Coinsence
 * @license https://www.humhub.com/licences
 *
 * @author Daly Ghaith <daly.ghaith@gmail.com>
 */

namespace humhub\modules\admin\controllers;

use humhub\modules\admin\permissions\ManageChallenges;
use humhub\modules\xcoin\models\Challenge;
use Yii;
use humhub\modules\admin\components\Controller;
use yii\data\ActiveDataProvider;
use yii\web\ForbiddenHttpException;
use yii\web\HttpException;

/**
 * ChallengeController provides management for spaces challenges.
 */
class ChallengeController extends Controller
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
        $this->subLayout = '@admin/views/layouts/challenge';
        $this->appendPageTitle(Yii::t('AdminModule.base', 'Challenges'));

        return parent::init();
    }

    /**
     * @inheritdoc
     */
    public function getAccessRules()
    {
        return [
            ['permissions' => [ManageChallenges::class]]
        ];
    }

    /**
     * Shows all available challenges
     * @throws ForbiddenHttpException
     */
    public function actionIndex()
    {
        if (!Yii::$app->user->can(new ManageChallenges())) {
            return $this->forbidden();
        }


        $dataProvider = new ActiveDataProvider([
            'query' => Challenge::find()
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Edit challenge
     * @throws ForbiddenHttpException
     * @throws HttpException
     */
    public function actionEdit()
    {
        if (!Yii::$app->user->can(new ManageChallenges())) {
            return $this->forbidden();
        }

        $model = Challenge::findOne(['id' => Yii::$app->request->get('id')]);

        if ($model == null) {
            throw new HttpException(404, Yii::t('AdminModule.controllers_ChallengeController', 'Challenge not found!'));
        }

        $model->scenario = Challenge::SCENARIO_EDIT_ADMIN;

        if ($model->load(Yii::$app->request->post()) && $model->save()) {

            $this->view->saved();

            return $this->htmlRedirect(['index']);
        }

        return $this->renderAjax('edit', ['model' => $model]);
    }
}
