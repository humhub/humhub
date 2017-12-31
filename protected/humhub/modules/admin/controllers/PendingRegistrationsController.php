<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

namespace humhub\modules\admin\controllers;

use humhub\modules\admin\components\Controller;
use humhub\modules\admin\models\PendingRegistrationSearch;
use humhub\modules\admin\permissions\ManageGroups;
use humhub\modules\admin\permissions\ManageUsers;
use humhub\modules\user\models\Invite;
use PHPExcel;
use PHPExcel_Cell;
use PHPExcel_Exception;
use PHPExcel_IOFactory;
use PHPExcel_Shared_Date;
use PHPExcel_Style_NumberFormat;
use Yii;
use yii\helpers\Url;
use yii\web\HttpException;

class PendingRegistrationsController extends Controller
{

    const EXPORT_CSV = 'csv';
    const EXPORT_XLSX = 'xsls';

    /**
     * Initializes the object.
     * This method is invoked at the end of the constructor after the object is initialized with the
     * given configuration.
     */
    public function init()
    {
        $this->subLayout = '@admin/views/layouts/user';
        $this->appendPageTitle(Yii::t('AdminModule.base', 'Pending user registrations'));
        return parent::init();
    }

    /**
     * Returns access rules for the standard access control behavior.
     *
     * @see AccessControl
     * @return array the access permissions
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


    /**
     * Render PendingRegistrations
     *
     * @param bool $export
     * @param null $format
     * @return string
     */
    public function actionIndex($export = false, $format = null)
    {
        $searchModel = new PendingRegistrationSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        $urlExportCsv = Url::to([
            'export',
            'format' => self::EXPORT_CSV,
            'PendingRegistrationSearch' => Yii::$app->request->get('PendingRegistrationSearch')
        ]);

        $urlExportXlsx = Url::to([
            'export',
            'format' => self::EXPORT_XLSX,
            'PendingRegistrationSearch' => Yii::$app->request->get('PendingRegistrationSearch')
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'searchModel' => $searchModel,
            'urlExportCsv' => $urlExportCsv,
            'urlExportXlsx' => $urlExportXlsx,
            'types' => $this->typeMapping(),
        ]);
    }

    /**
     * Export PendingRegistrations
     *
     * @param string $format
     * @throws PHPExcel_Exception
     */
    public function actionExport($format)
    {
        $searchModel = new PendingRegistrationSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        $columns = [
            'email',
            'originator.username',
            'language',
            'source',
            'created_at',
        ];

        $title = Yii::t(
            'AdminModule.base',
            'Pending user registrations'
        );

        $file = new PHPExcel();
        $file->getProperties()
            ->setCreator('HumHub')
            ->setTitle($title)
            ->setSubject($title)
            ->setDescription($title);

        $worksheet = $file->getActiveSheet();
        $worksheet->setTitle($title);

        // Row counter
        $row = 1;

        // Set format for Date fields
        $formatDate = $format === self::EXPORT_CSV
            ? Yii::$app->formatter->getDateTimePattern
            : PHPExcel_Style_NumberFormat::FORMAT_DATE_DATETIME;

        // Build Header
        for ($i = 0; $i < count($columns); $i++) {
            $worksheet->getColumnDimension(PHPExcel_Cell::stringFromColumnIndex($i))->setWidth(30);
            $worksheet->setCellValueByColumnAndRow($i, $row, $searchModel->getAttributeLabel($columns[$i]));
        }

        // Build Rows
        foreach ($dataProvider->query->all() as $record) {
            $row++; // Increase counter

            for ($i = 0; $i < count($columns); $i++) {
                $name = $columns[$i];
                $value = $record->{$name};

                if ($name === 'source') {
                    $typeMapping = $this->typeMapping();
                    $value = isset($typeMapping[$value]) ? $typeMapping[$value] : $value;
                }

                if ($name === 'created_at') {
                    $worksheet->getStyleByColumnAndRow($i, $row)->getNumberFormat()->setFormatCode($formatDate);
                    $value = PHPExcel_Shared_Date::PHPToExcel(new \DateTime($value));
                }

                $worksheet->setCellValueByColumnAndRow($i, $row, $value);
            }
        }

        $filename = 'pur_export_' . time();

        if ($format === self::EXPORT_CSV) {

            /** @var \PHPExcel_Writer_CSV $writer */
            $writer = PHPExcel_IOFactory::createWriter($file, 'CSV');
            $writer->setDelimiter(';');

            header('Content-Type: application/csv');
            header('Content-Disposition: attachment;filename="' . $filename . '.csv"');
            header('Cache-Control: max-age=0');

        } else {

            /** @var \PHPExcel_Writer_Excel2007 $writer */
            $writer = PHPExcel_IOFactory::createWriter($file, 'Excel2007');

            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
            header('Cache-Control: max-age=0');

        }

        $writer->save('php://output');
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
        $invite = Invite::findOne(['id' => $id]);
        if ($invite === null) {
            throw new HttpException(404, Yii::t(
                'AdminModule.controllers_PendingRegistrationsController',
                'Invite not found!'
            ));
        }

        if (Yii::$app->request->isPost) {
            $invite->sendInviteMail();
            $invite->save();
            $invite->refresh();
            $this->view->success(Yii::t(
                'AdminModule.controllers_PendingRegistrationsController',
                'Resend invitation email'
            ));
        }

        return $this->render('resend', ['model' => $invite]);
    }

    /**
     * Return type mapping
     *
     * @return array
     */
    private function typeMapping()
    {
        return [
            PendingRegistrationSearch::SOURCE_INVITE => Yii::t('AdminModule.base', 'Invite'),
            PendingRegistrationSearch::SOURCE_SELF => Yii::t('AdminModule.base', 'Sign up'),
        ];
    }

}