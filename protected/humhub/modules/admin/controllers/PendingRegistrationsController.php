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
use PHPExcel_Worksheet;
use Yii;
use yii\helpers\Url;
use yii\web\HttpException;

class PendingRegistrationsController extends Controller
{

    const EXPORT_CSV = 'csv';
    const EXPORT_XLSX = 'xsls';
    const EXPORT_PREFIX = 'pur_export';

    const EXPORT_COLUMNS = [
        'email',
        'originator.username',
        'language',
        'source',
        'created_at',
    ];

    /**
     * @inheritdoc
     */
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

        /** @var PHPExcel $file */
        $file = $this->createCsvFile();

        /** @var PHPExcel_Worksheet $worksheet */
        $worksheet = $file->getActiveSheet();

        // Row counter
        $rowCount = 1;

        // Build Header
        $this->buildCsvHeaderRow($worksheet, $rowCount, $searchModel);

        // Set format for Date fields
        $formatDate = $format === self::EXPORT_CSV
            ? Yii::$app->formatter->getDateTimePattern()
            : PHPExcel_Style_NumberFormat::FORMAT_DATE_DATETIME;

        // Build Rows
        foreach ($dataProvider->query->all() as $record) {
            $rowCount++;
            $this->buildCsvRow($rowCount, $record, $worksheet, $formatDate);
        }

        if ($format === self::EXPORT_CSV) {
            $this->exportAsCsv($file);
        } else {
            $this->exportAsXlsx($file);
        }
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

    /**
     * Export the file as Csv
     *
     * @param PHPExcel $file
     * @throws \PHPExcel_Reader_Exception
     * @throws \PHPExcel_Writer_Exception
     */
    private function exportAsCsv($file)
    {
        header('Content-Type: application/csv');
        header('Content-Disposition: attachment;filename="' . self::EXPORT_PREFIX . '_' . time() . '.csv"');
        header('Cache-Control: max-age=0');

        /** @var \PHPExcel_Writer_CSV $writer */
        $writer = PHPExcel_IOFactory::createWriter($file, 'CSV');
        $writer->setDelimiter(';');
        $writer->save('php://output');
    }

    /**
     * Export the file as Xlsx
     *
     * @param PHPExcel $file
     * @throws \PHPExcel_Reader_Exception
     * @throws \PHPExcel_Writer_Exception
     */
    private function exportAsXlsx($file)
    {
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . self::EXPORT_PREFIX . '_' . time() . '.xlsx"');
        header('Cache-Control: max-age=0');

        /** @var \PHPExcel_Writer_Excel2007 $writer */
        $writer = PHPExcel_IOFactory::createWriter($file, 'Excel2007');
        $writer->save('php://output');
    }

    /**
     * Build a row for csv document
     *
     * @param integer $row
     * @param PendingRegistrationSearch $record
     * @param PHPExcel_Worksheet $worksheet
     * @param string $formatDate
     */
    private function buildCsvRow($row, $record, $worksheet, $formatDate)
    {
        for ($i = 0; $i < count(self::EXPORT_COLUMNS); $i++) {
            $name = self::EXPORT_COLUMNS[$i];
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

    /**
     * Build header row for csv document
     *
     * @param PHPExcel_Worksheet $worksheet
     * @param integer $row
     * @param PendingRegistrationSearch $searchModel
     */
    private function buildCsvHeaderRow($worksheet, $row, $searchModel)
    {
        for ($i = 0; $i < count(self::EXPORT_COLUMNS); $i++) {
            $worksheet->getColumnDimension(PHPExcel_Cell::stringFromColumnIndex($i))->setWidth(30);
            $worksheet->setCellValueByColumnAndRow($i, $row, $searchModel->getAttributeLabel(self::EXPORT_COLUMNS[$i]));
        }
    }

    /**
     * Return new PHPExcel file
     *
     * @return PHPExcel
     */
    private function createCsvFile()
    {
        $title = Yii::t(
            'AdminModule.base',
            'Pending user registrations'
        );

        /** @var PHPExcel $file */
        $file = new PHPExcel();
        $file->getProperties()
            ->setCreator('HumHub')
            ->setTitle($title)
            ->setSubject($title)
            ->setDescription($title);
        return $file;
    }
}
