<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\tests\codeception\unit;

use Codeception\Test\Unit;
use humhub\components\export\ExportResult;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

/**
 * Class ExportResultTest
 *
 * This class was originally developed by Paul Klimov <klimov.paul@gmail.com> and his
 * project csv-grid (https://github.com/yii2tech/csv-grid).
 */
class ExportResultTest extends Unit
{
    const TEST_FILE = 'test.csv';

    /**
     * @var \UnitTester
     */
    protected $tester;

    /**
     * @Override
     */
    protected function _after()
    {
        if (is_writable(self::TEST_FILE)) {
            unlink(self::TEST_FILE);
        }
    }

    /**
     * @param array $config
     * @return ExportResult export result instance
     */
    protected function createExportResult($config = [])
    {
        $exportResult = new ExportResult($config);
        $exportResult->basePath = self::TEST_FILE;
        return $exportResult;
    }

    /**
     * Test new Spreadsheet
     */
    public function testNewSpreadsheet()
    {
        $exportResult = $this->createExportResult();

        $spreadsheet = $exportResult->newSpreadsheet();
        $this->assertTrue($spreadsheet instanceof Spreadsheet);
    }

    /**
     * @depends testNewSpreadsheet
     */
    public function testResultFileName()
    {
        $exportResult = $this->createExportResult([
            'fileBaseName' => 'newname',
        ]);

        $this->assertEquals('newname.csv', $exportResult->getResultFileName());
    }
}
