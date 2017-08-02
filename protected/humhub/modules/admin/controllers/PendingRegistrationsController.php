<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

/**
 * Created by PhpStorm.
 * User: buddha
 * Date: 31.07.2017
 * Time: 13:25
 */

namespace humhub\modules\admin\controllers;


use DateTime;
use humhub\components\ActiveRecord;
use humhub\modules\admin\components\Controller;
use humhub\modules\admin\models\PendingRegistrationSearch;
use humhub\modules\admin\permissions\ManageGroups;
use humhub\modules\admin\permissions\ManageUsers;
use PHPExcel;
use PHPExcel_Cell;
use PHPExcel_IOFactory;
use PHPExcel_Shared_Date;
use PHPExcel_Style_NumberFormat;
use PHPExcel_Writer_Excel2007;
use function PHPSTORM_META\type;
use Yii;
use yii\helpers\ArrayHelper;

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

    public function actionIndex($export = false, $format = null)
    {
        $searchModel = new PendingRegistrationSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        if($export) {
            return $this->createCVS($dataProvider, $searchModel, $format);
        }


        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'searchModel' => $searchModel,
            'types' => $this->getTypeMapping()
        ]);
    }

    public function getTypeMapping()
    {
        return [
            PendingRegistrationSearch::SOURCE_INVITE => Yii::t('AdminModule.base', 'Invite'),
            PendingRegistrationSearch::SOURCE_SELF => Yii::t('AdminModule.base', 'Sign up'),
        ];
    }

    public function createCVS($dataProvider, ActiveRecord $model, $format = null)
    {
        $columns = [
            ['email'],
            ['originator.username'],
            ['language'],
            ['source'],
            ['created_at', 'type' => 'datetime'],
        ];

        $file = new PHPExcel();
        $file->getProperties()->setCreator('HumHub');
        $file->getProperties()->setTitle(Yii::t('AdminModule.base', 'Pending user registrations'));
        $file->getProperties()->setSubject(Yii::t('AdminModule.base', 'Pending user registrations'));
        $file->getProperties()->setDescription(Yii::t('AdminModule.base', 'Pending user registrations'));

        $file->setActiveSheetIndex(0);
        $worksheet = $file->getActiveSheet();

        $worksheet->setTitle(Yii::t('AdminModule.base', 'Pending user registrations'));

        // Creat header
        $row = 1;
        $lastColumn = count($columns);
        for ($column = 0; $column != $lastColumn; $column++) {
            $columnKey = PHPExcel_Cell::stringFromColumnIndex($column);
            $worksheet->getColumnDimension($columnKey)->setWidth(30);
            $worksheet->setCellValueByColumnAndRow($column, $row, $model->getAttributeLabel($columns[$column][0]));
        }

        $row++;

        // Fill content header
        foreach($dataProvider->query->all() as $record) {
            for ($column = 0; $column != $lastColumn; $column++) {
                $attribute = $columns[$column][0];
                $value = ArrayHelper::getValue($record,$attribute);

                if(isset($columns[$column]['type']) && $columns[$column]['type'] === 'datetime') {
                    $value = PHPExcel_Shared_Date::PHPToExcel(new DateTime($value));
                    if($format === 'CSV') {
                        $worksheet->getStyleByColumnAndRow($column, $row)->getNumberFormat()->setFormatCode(Yii::$app->formatter->getDateTimePattern());
                    } else {
                        $worksheet->getStyleByColumnAndRow($column, $row)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_DATE_DATETIME);
                    }
                }

                if($attribute === 'source') {
                    $types = $this->getTypeMapping();
                    $value = isset($types[$value]) ? $types[$value] : $value;
                }

                $worksheet->setCellValueByColumnAndRow($column, $row, $value);
            }
            $row++;
        }

        $filePrefix = 'pur_export_'.time();
        if($format === 'CSV') {
            $writer = PHPExcel_IOFactory::createWriter($file, 'CSV');
            $writer->setDelimiter(';');

            header('Content-Type: application/csv');
            header('Content-Disposition: attachment;filename="'.$filePrefix.'.csv"');
            header('Cache-Control: max-age=0');
        } else {
            $writer = PHPExcel_IOFactory::createWriter($file, 'Excel2007');
            header('Content-type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment; filename="'.$filePrefix.'.xlsx"');
            header('Cache-Control: max-age=0');
        }

        $writer->save('php://output');
    }
}