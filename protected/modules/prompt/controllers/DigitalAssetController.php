<?php
// controllers/DigitalAssetController.php
namespace app\humhub\modules\prompt\controllers;

use humhub\components\Controller;
use Yii;
use app\models\DigitalAsset;
use yii\web\NotFoundHttpException;

class DigitalAssetController extends Controller
{
    public function actionIndex()
    {
        $assets = DigitalAsset::find()->all();
        return $this->render('index', ['assets' => $assets]);
    }

    public function actionView($id)
    {
        return $this->render('view', ['model' => $this->findModel($id)]);
    }

    public function actionCreate()
    {
        $model = new DigitalAsset();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('create', ['model' => $model]);
    }

    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', ['model' => $model]);
    }

    public function actionDelete($id)
    {
        $this->findModel($id)->delete();
        return $this->redirect(['index']);
    }

    protected function findModel($id)
    {
        if (($model = DigitalAsset::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}