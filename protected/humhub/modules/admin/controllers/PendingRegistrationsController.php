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
use Throwable;
use Yii;
use yii\base\Exception;
use yii\web\HttpException;
use yii\web\Response;

class PendingRegistrationsController extends Controller
{
    /**
     * @inheritdoc
     */
    public $adminOnly = false;

    /**
     * @inheritDoc
     */
    public function init()
    {
        $this->subLayout = '@admin/views/layouts/user';
        $this->appendPageTitle(Yii::t('AdminModule.base', 'Pending user registrations'));

        parent::init();
    }

    /**
     * @inheritdoc
     */
    protected function getAccessRules()
    {
        return [
            [
                'permission' => [
                    ManageUsers::class,
                    ManageGroups::class,
                ],
            ],
        ];
    }

    public function actionIndex()
    {
        $searchModel = new PendingRegistrationSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'searchModel' => $searchModel,
            'types' => [null => null] + $searchModel->getAllowedSources(),
        ]);
    }

    /**
     * Export user list as csv or xlsx
     *
     * @param string $format supported format by phpspreadsheet
     * @return Response
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     * @throws Exception
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
     * Resend an invitation
     *
     * @param int $id
     * @return string
     * @throws HttpException
     */
    public function actionResend($id)
    {
        $this->forcePostRequest();
        $invite = $this->findInviteById($id);
        if (Yii::$app->request->isPost) {
            if ($invite->sendInviteMail()) {
                $this->view->success(Yii::t('AdminModule.user', 'Resend invitation email'));
            } else {
                $this->view->error(Yii::t('AdminModule.user', 'Cannot resend invitation email!'));
            }
            return $this->redirect(['index']);
        }
        return $this->render('resend', ['model' => $invite]);
    }

    /**
     * Delete an invitation
     *
     * @param int $id
     * @return string
     * @throws HttpException
     * @throws Throwable
     */
    public function actionDelete($id)
    {
        $this->forcePostRequest();
        $invite = $this->findInviteById($id);
        if (Yii::$app->request->isPost) {
            if ($invite->delete()) {
                $this->view->success(Yii::t(
                    'AdminModule.user',
                    'Deleted invitation',
                ));
            }
            return $this->redirect(['index']);
        }
        return $this->render('delete', ['model' => $invite]);
    }

    /**
     * Resend all invitations
     *
     * @return string
     * @throws HttpException
     * @throws Throwable
     */
    public function actionResendAll()
    {
        if (Yii::$app->request->isPost) {
            foreach (Invite::find()->where(Invite::filterSource())->each() as $invite) {
                $invite->sendInviteMail();
            }

            $this->view->success(Yii::t(
                'AdminModule.user',
                'All open registration invitations were successfully re-sent.',
            ));
        }
        return $this->redirect(['index']);
    }

    /**
     * Delete all invitations
     *
     * @param int $id
     * @return string
     * @throws HttpException
     * @throws Throwable
     */
    public function actionDeleteAll()
    {
        if (Yii::$app->request->isPost) {
            Invite::deleteAll(Invite::filterSource());

            $this->view->success(Yii::t(
                'AdminModule.user',
                'All open registration invitations were successfully deleted.',
            ));
        }
        return $this->redirect(['index']);
    }

    /**
     * Resend all or selected invitation
     *
     * @return string
     * @throws HttpException
     * @throws Throwable
     */
    public function actionResendAllSelected()
    {
        if (Yii::$app->request->isPost) {

            $ids = Yii::$app->request->post('id');
            if (!empty($ids)) {
                foreach (Invite::findAll(['id' => $ids] + Invite::filterSource()) as $invite) {
                    $invite->sendInviteMail();
                }
                $this->view->success(Yii::t(
                    'AdminModule.user',
                    'The selected invitations have been successfully re-sent!',
                ));
            }
        }
        return $this->redirect(['index']);
    }

    /**
     * Delete all or selected invitation
     *
     * @return string
     * @throws HttpException
     * @throws Throwable
     */
    public function actionDeleteAllSelected()
    {
        if (Yii::$app->request->isPost) {
            $ids = Yii::$app->request->post('id');
            if (!empty($ids)) {
                foreach (Invite::findAll(['id' => $ids] + Invite::filterSource()) as $invite) {
                    $invite->delete();
                }
                $this->view->success(Yii::t(
                    'AdminModule.user',
                    'The selected invitations have been successfully deleted!',
                ));
            }
        }
        return $this->redirect(['index']);
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
        $invite = Invite::findOne(['id' => $id] + Invite::filterSource());
        if ($invite === null) {
            throw new HttpException(404, Yii::t(
                'AdminModule.user',
                'Invite not found!',
            ));
        }
        return $invite;
    }
}
