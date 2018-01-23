<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

namespace humhub\modules\admin\controllers;

use humhub\components\export\DateTimeColumn;
use humhub\components\export\SpreadsheetExport;
use humhub\modules\admin\components\Controller;
use humhub\modules\admin\models\PendingRegistrationSearch;
use humhub\modules\admin\permissions\ManageGroups;
use humhub\modules\admin\permissions\ManageUsers;
use Yii;

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
     * @throws \yii\base\InvalidConfigException
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
                'class' => DateTimeColumn::className(),
                'attribute' => 'created_at',
            ],
            'created_by',
            [
                'class' => DateTimeColumn::className(),
                'attribute' => 'updated_at',
            ],
            'updated_by',
            'language',
            'firstname',
            'lastname',
        ];
    }
}