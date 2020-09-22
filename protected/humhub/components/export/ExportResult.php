<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\components\export;

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use Yii;
use yii\base\BaseObject;
use yii\helpers\FileHelper;
use yii\web\Response;

/**
 * ExportResult represents SpreadsheetExport result.
 *
 * This class was originally developed by Paul Klimov <klimov.paul@gmail.com> and his
 * project csv-grid (https://github.com/yii2tech/csv-grid).
 *
 * @see SpreadsheetExport
 */
class ExportResult extends BaseObject
{

    /**
     * @var string base path for the temporary directory and files.
     */
    public $basePath = '@runtime/data_export';

    /**
     * @var string base name, which should be used for the created files.
     */
    public $fileBaseName = 'export';

    /**
     * @var string
     */
    public $writerType = 'csv';

    /**
     * @var Spreadsheet Spreadsheet instance
     */
    private $spreadsheet;

    /**
     * @var string temporary files directory name
     */
    private $tempFileName;

    /**
     * @var string name of the result file.
     */
    private $resultFileName;

    /**
     * Destructor.
     * Makes sure the temporary directory removed.
     */
    public function __destruct()
    {
        $this->delete();
    }

    /**
     * @return string files directory name
     * @throws \yii\base\Exception
     */
    public function getTempFileName()
    {
        if ($this->tempFileName === null) {
            $basePath = Yii::getAlias($this->basePath);
            FileHelper::createDirectory($basePath);
            $this->tempFileName = $basePath . DIRECTORY_SEPARATOR . uniqid(time(), true);
        }
        return $this->tempFileName;
    }

    /**
     * @return string result file name
     */
    public function getResultFileName()
    {
        if ($this->resultFileName === null) {
            $this->resultFileName = $this->fileBaseName . '.' . $this->writerType;
        }
        return $this->resultFileName;
    }

    /**
     * Creates new CSV file in result set.
     * @return Spreadsheet instance.
     */
    public function newSpreadsheet()
    {
        $this->spreadsheet = new Spreadsheet();
        return $this->spreadsheet;
    }

    /**
     * Deletes associated directory with all internal files.
     * @return boolean whether file has been deleted.
     */
    public function delete()
    {
        if (!empty($this->tempFileName) && is_writable($this->tempFileName)) {
            FileHelper::unlink($this->tempFileName);
            return true;
        }
        return false;
    }

    /**
     * Saves this file.
     * @param string $file destination file name (may content path alias).
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     */
    public function saveAs($file)
    {
        $writer = IOFactory::createWriter($this->spreadsheet, ucfirst($this->writerType));
        $writer->save($file);
    }

    /**
     * Prepares response for sending a result file to the browser.
     * Note: this method works only while running web application.
     * @param array $options additional options for sending the file. See [[\yii\web\Response::sendFile()]] for details.
     * @return \yii\web\Response application response instance.
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     * @throws \yii\base\Exception
     */
    public function send($options = [])
    {
        $this->saveAs($this->getTempFileName());

        $response = Yii::$app->getResponse();
        $response->on(Response::EVENT_AFTER_SEND, [$this, 'delete']);

        return $response->sendFile($this->getTempFileName(), $this->getResultFileName(), $options);
    }
}
