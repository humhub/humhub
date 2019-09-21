<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\admin\controllers;

use humhub\components\export\DateTimeColumn;
use humhub\components\export\SpreadsheetExport;
use humhub\modules\admin\components\Controller;
use humhub\modules\admin\models\PendingRegistrationSearch;
use humhub\modules\admin\permissions\ManageGroups;
use humhub\modules\admin\permissions\ManageUsers;
use humhub\modules\user\models\Invite;
use Yii;
use yii\web\HttpException;

class PendingRegistrationsController extends Controller
{
    public function init()
    {
        $this->subLayout = '@admin/views/layouts/user';
        $this->appendPageTitle(Yii::t('AdminModule.base', 'Pending user registrations'));
        return parent::init();
    }

    /**
     * @inheritdoc
     */
    public function getAccessRules()
    {
        return [
            [
                'permission' => [
                    ManageUsers::class,
                    ManageGroups::class,
                ]
            ]
        ];
    }

    public function actionIndex()
    {
        $searchModel = new PendingRegistrationSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'searchModel' => $searchModel,
            'types' => [
                null => null,
                PendingRegistrationSearch::SOURCE_INVITE => Yii::t('AdminModule.base', 'Invite'),
                PendingRegistrationSearch::SOURCE_SELF => Yii::t('AdminModule.base', 'Sign up'),
            ]
        ]);
    }

    /**
     * Export user list as csv or xlsx
     * @param string $format supported format by phpspreadsheet
     * @return \yii\web\Response
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     * @throws \yii\base\Exception
     */
    public function actionExport($format)
    {
        $searchModel = new PendingRegistrationSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        $exporter = new SpreadsheetExport([
            'dataProvider' => $dataProvider,
            'columns' => $this->collectExportColumns(),
            'resultConfig' => [
                'fileBaseName' => 'humhub_user',
                'writerType' => $format,
            ],
        ]);

        return $exporter->export()->send();
    }

    /**
     * Resend a invite
     *
     * @param integer $id
     * @return string
     * @throws HttpException
     */
    public function actionResend($id)
    {
        $invite = $this->findInviteById($id);
        if (Yii::$app->request->isPost) {
            $invite->sendInviteMail();
            $this->view->success(Yii::t(
                'AdminModule.user',
                'Resend invitation email'
            ));
            return $this->redirect(['index']);
        }
        return $this->render('resend', ['model' => $invite]);
    }

    /**
     * Delete an invite
     *
     * @param integer $id
     * @return string
     * @throws HttpException
     * @throws \Throwable
     */
    public function actionDelete($id)
    {
        $invite = $this->findInviteById($id);
        if (Yii::$app->request->isPost) {
            $invite->delete();
            $this->view->success(Yii::t(
                'AdminModule.user',
                'Deleted invitation'
            ));
            return $this->redirect(['index']);
        }
        return $this->render('delete', ['model' => $invite]);
    }

    /**
     * Return array with columns for data export
     * @return array
     */
    private function collectExportColumns()
    {
        return [
            'id',
            'user_originator_id',
            'space_invite_id',
            'email',
            'source',
            [
                'class' => DateTimeColumn::class,
                'attribute' => 'created_at',
            ],
            'created_by',
            [
                'class' => DateTimeColumn::class,
                'attribute' => 'updated_at',
            ],
            'updated_by',
            'language',
            'firstname',
            'lastname',
        ];
    }

    /**
     * Find invite by id
     * @param $id
     * @return Invite|null
     * @throws HttpException
     */
    private function findInviteById($id)
    {
        $invite = Invite::findOne(['id' => $id]);
        if ($invite === null) {
            throw new HttpException(404, Yii::t(
                'AdminModule.user',
                'Invite not found!'
            ));
        }
        return $invite;
    }
}
